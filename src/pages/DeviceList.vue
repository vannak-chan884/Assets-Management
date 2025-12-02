<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import api from "../api";

const propertyNumberPattern = /^[A-Z]{2}\d{4}[A-Z]{3}\d{5}[A-Z]$/;
const deviceIdError = ref("");

const devices = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editDeviceIdError = ref("");

const currentPage = ref(1);
const perPage = ref(10); // Number of rows per page
const perPageOptions = [5, 10, 20, 50, 100];
const search = ref("");
const filterBy = ref("all");

// Sortable column headers
const sortField = ref(null);       // "device_id", "device_name", "description"
const sortDirection = ref("asc");  // asc | desc

// Loading spinner
const isLoading = ref(false);

const form = reactive({
  device_id: "",
  device_name: "",
  description: "",
});


// Multi-select delete
const selected = ref([]);

// Select all toggle
const selectAll = computed({
  get() {
    // return true only if ALL rows on current page are selected
    return (
      paginatedDevices.value.length > 0 &&
      paginatedDevices.value.every((d) => selected.value.includes(d.device_id))
    );
  },
  set(value) {
    if (value) {
      // Add only current page rows
      const pageIDs = paginatedDevices.value.map((d) => d.device_id);

      selected.value = Array.from(new Set([...selected.value, ...pageIDs]));
    } else {
      // Remove only current page rows
      const pageIDs = paginatedDevices.value.map((d) => d.device_id);
      selected.value = selected.value.filter((id) => !pageIDs.includes(id));
    }
  },
});

// For editing
const editing = ref(null);

watch(() => form.device_id, (value) => {
  if (!propertyNumberPattern.test(value)) {
    deviceIdError.value = "Invalid format. Like this 👉: KH7325EOJ00002B";
  } else {
    deviceIdError.value = "";
  }
});

// Validate form
const isFormValid = computed(() => {
  return form.device_id && form.device_name && propertyNumberPattern.test(form.device_id);
});

// Fetch devices
const fetchDevices = async () => {
  try {
    const res = await api.get("/devices");
    devices.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

onMounted(fetchDevices);

// Create device
const create = async () => {
  try {
    await api.post("/devices", form);

    // Reset form
    Object.assign(form, {
      device_id: "",
      device_name: "",
      description: "",
    });

    showCreateModal.value = false;
    fetchDevices();
  } catch (err) {
    console.error(err);
  }
};

// Edit
const edit = (d) => {
  editing.value = { ...d };
  showEditModal.value = true;
};

// Update device
const update = async () => {
  try {
    await api.put(`/devices/${editing.value.device_id}`, editing.value);
    showEditModal.value = false;
    editing.value = null;
    fetchDevices();
  } catch (err) {
    console.error(err);
  }
};

watch(() => editing?.value?.device_id, (value) => {
  if (!value) return;

  if (!propertyNumberPattern.test(value)) {
    editDeviceIdError.value = "Invalid format. Example: KH7325EOJ00002B";
  } else {
    editDeviceIdError.value = "";
  }
});

// Delete only one row
const remove = async (id) => {
  if (!confirm("Delete this device?")) return;

  try {
    await api.delete(`/devices/${id}`);
    fetchDevices();
  } catch (err) {
    console.error(err);
  }
};

// Delete multi-rows
const deleteSelected = async () => {
  if (selected.value.length === 0) return;

  if (!confirm(`Delete ${selected.value.length} device(s)?`)) return;

  try {
    await Promise.all(
      selected.value.map((id) => api.delete(`/devices/${id}`))
    );

    selected.value = [];
    fetchDevices();
  } catch (err) {
    console.error(err);
  }
};

// Pagination
// Auto scroll to top of table when page changes
watch(currentPage, () => {
  const table = document.getElementById("device-table");
  if (table) {
    table.scrollIntoView({ behavior: "smooth" });
  }
});

const filteredDevices = computed(() => {
  const term = search.value.toLowerCase();

  return devices.value.filter((d) => {
    const idMatch = d.device_id.toString().toLowerCase().includes(term);
    const nameMatch = d.device_name.toLowerCase().includes(term);

    if (filterBy.value === "id") return idMatch;
    if (filterBy.value === "name") return nameMatch;

    return idMatch || nameMatch; // default = all
  });
});


watch(devices, () => {
  // If current page becomes empty, go to previous page
  if (paginatedDevices.value.length === 0 && currentPage.value > 1) {
    currentPage.value--;
  }
});

const paginatedDevices = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  // return filteredDevices.value.slice(start, start + perPage.value);
  return sortedDevices.value.slice(start, start + perPage.value);
});

const totalPages = computed(() => {
  return Math.ceil(filteredDevices.value.length / perPage.value) || 1;
});

const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    await triggerLoading();
  }
};

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    await triggerLoading();
  }
};

// Reset to page 1 when rows-per-page changes
watch([perPage, search, filterBy], async () => {
  currentPage.value = 1;

  // clear old selections when page size changes
  selected.value = [];

  await triggerLoading(); // 👈 show spinner
});

const sortBy = (field) => {
  if (sortField.value === field) {
    // Toggle asc/desc
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortDirection.value = "asc";   // default direction
  }
};

const sortedDevices = computed(() => {
  const list = [...filteredDevices.value];

  if (!sortField.value) return list;

  return list.sort((a, b) => {
    const x = a[sortField.value]?.toString().toLowerCase() || "";
    const y = b[sortField.value]?.toString().toLowerCase() || "";

    if (x < y) return sortDirection.value === "asc" ? -1 : 1;
    if (x > y) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});

watch(sortedDevices, () => {
  if (paginatedDevices.value.length === 0 && currentPage.value > 1) {
    currentPage.value = totalPages.value;
  }
});

// Loading Spinner...
const triggerLoading = async () => {
  isLoading.value = true;

  // OPTIONAL: simulate network delay (300ms)
  await new Promise((resolve) => setTimeout(resolve, 300));

  isLoading.value = false;
};

</script>
<template>
  <div class="relative mx-4">
    <div class="sticky top-18 bg-white py-4 z-50">
      <h2 class="text-center text-2xl font-bold uppercase">Device List</h2>
      <div class="flex justify-between items-center space-x-4 mt-4">
        <div class="flex items-center space-x-4">
          <button @click="showCreateModal = true"
            class="w-32 bg-blue-500 hover:bg-blue-600 text-white font-bold px-3 py-2 rounded-md">
            Add Device
          </button>
          <button v-if="selected.length > 0" @click="deleteSelected" class="bg-red-600 text-white px-3 py-2 rounded-md">
            Delete All
          </button>
        </div>
        <div class="flex items-center space-x-4">
          <!-- Search box -->
          <input v-model="search" placeholder="Search device..." class="border px-3 py-2 rounded w-64" />

          <!-- Filter dropdown -->
          <select v-model="filterBy" class="border px-3 py-2 rounded">
            <option value="all">All fields</option>
            <option value="id">Property Number</option>
            <option value="name">Device Name</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Create Device Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white w-[450px] p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-bold mb-4">Add Device</h2>

        <div class="space-y-3">
          <div>
            <label class="font-medium">Device ID</label>
            <input v-model="form.device_id" class="w-full border px-3 py-2 rounded-md" />
            <p v-if="deviceIdError" class="text-red-600 text-sm mt-1">
              {{ deviceIdError }}
            </p>
          </div>

          <div>
            <label class="font-medium">Device Name</label>
            <input v-model="form.device_name" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Description</label>
            <input v-model="form.description" class="w-full border px-3 py-2 rounded-md" />
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <button @click="showCreateModal = false"
            class="bg-red-100 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-md">
            Cancel
          </button>

          <button @click="create" :disabled="!isFormValid"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:bg-gray-300">
            Create
          </button>
        </div>
      </div>
    </div>

    <div class="relative">
      <table class="w-full border-collapse text-sm mt-4" id="device-table">
        <thead class="bg-gray-400">
          <tr class="text-center text-md font-semibold">
            <th class="border border-gray-300 p-4">
              <input type="checkbox" v-model="selectAll" />
            </th>
            <th @click="sortBy('device_id')"
              class="border border-gray-300 p-4 font-semibold text-gray-900 cursor-pointer">
              Property Number
              <span v-if="sortField === 'device_id'">
                {{ sortDirection === 'asc' ? '▲' : '▼' }}
              </span>
            </th>

            <th @click="sortBy('device_name')"
              class="border border-gray-300 p-4 font-semibold text-gray-900 cursor-pointer">
              Device Name
              <span v-if="sortField === 'device_name'">
                {{ sortDirection === 'asc' ? '▲' : '▼' }}
              </span>
            </th>

            <th @click="sortBy('description')"
              class="border border-gray-300 p-4 font-semibold text-gray-900 cursor-pointer">
              Description
              <span v-if="sortField === 'description'">
                {{ sortDirection === 'asc' ? '▲' : '▼' }}
              </span>
            </th>
            <th class="border border-gray-300 p-4 font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in paginatedDevices" :key="d.device_id" class="odd:bg-gray-200 even:bg-gray-300 text-center">
            <td class="w-[5%] border border-gray-300 text-gray-500 text-center">
              <input type="checkbox" v-model="selected" :value="d.device_id" />
            </td>
            <td class="w-[10%] border border-gray-300 text-gray-500">{{ d.device_id }}</td>
            <td class="w-[30%] border border-gray-300 text-left px-4 text-gray-500">
              {{ d.device_name }}
            </td>
            <td class="w-[40%] border border-gray-300 text-left px-4 text-gray-500">
              {{ d.description }}
            </td>
            <td class="w-[15%] border border-gray-300 text-gray-500">
              <button @click="edit(d)"
                class="bg-yellow-500 hover:not-focus:bg-yellow-700 text-white font-medium mr-4 my-1 px-4 py-2 rounded-md cursor-pointer">
                Edit
              </button>
              <button @click="remove(d.device_id)"
                class="bg-red-500 hover:not-focus:bg-red-700 text-white font-medium my-1 px-4 py-2 rounded-md cursor-pointer">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Loading Spinner -->
      <div v-if="isLoading"
        class="absolute w-full h-[89.5%] top-[10.5%] left-0 py-4 flex justify-center items-center bg-gray-800/20">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-red-500 border-t-transparent"></div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between my-4">
      <!-- Rows Per Page dropdown -->
      <div class="flex items-center space-x-2">
        <span class="font-medium">Rows per page:</span>
        <select v-model="perPage" class="border px-2 py-1 rounded">
          <option v-for="opt in perPageOptions" :value="opt" :key="opt">
            {{ opt }}
          </option>
        </select>
      </div>

      <!-- Pagination buttons -->
      <div class="flex items-center space-x-4">
        <button @click="prevPage" :disabled="currentPage === 1"
          class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50">
          Previous
        </button>

        <span class="font-semibold">
          Page {{ currentPage }} of {{ totalPages }}
        </span>

        <button @click="nextPage" :disabled="currentPage === totalPages"
          class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50">
          Next
        </button>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/60 bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white w-[450px] p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-bold mb-4">Edit Device</h2>

        <div class="space-y-3">
          <div>
            <label class="font-medium">Property Number</label>
            <input v-model="editing.device_id" class="w-full border px-3 py-2 rounded-md" />
            <p v-if="editDeviceIdError" class="text-red-600 text-sm mt-1">
              {{ editDeviceIdError }}
            </p>
          </div>

          <div>
            <label class="font-medium">Device Name</label>
            <input v-model="editing.device_name" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Description</label>
            <input v-model="editing.description" class="w-full border px-3 py-2 rounded-md" />
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <button @click="showEditModal = false"
            class="bg-red-100 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-md">
            Cancel
          </button>

          <button @click="update" :disabled="!!editDeviceIdError"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:bg-gray-300">
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
