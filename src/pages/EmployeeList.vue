<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import api from "../api";

// State
const employees = ref([]);
const showCreateModal = ref(false);
const showEditModal = ref(false);

const form = reactive({
  user_id: "",
  user_name: "",
  gender: "",
  department_id: "",
  join_date: "",
});

// For editing
const editing = ref(null);

// Validate form
const isFormValid = computed(() => {
  return form.user_id && form.user_name && form.gender && form.department_id;
});

// Fetch employees
const fetchEmployees = async () => {
  try {
    const res = await api.get("/employees");
    employees.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

onMounted(fetchEmployees);

// Create employee
const create = async () => {
  try {
    await api.post("/employees", form);

    // Reset form
    Object.assign(form, {
      user_id: "",
      user_name: "",
      gender: "",
      department_id: "",
      join_date: "",
    });

    showCreateModal.value = false; // 🔥 close modal
    fetchEmployees();
  } catch (err) {
    console.error(err);
  }
};

// Edit
const edit = (e) => {
  editing.value = { ...e };
  showEditModal.value = true;
};

// Update employee
const update = async () => {
  try {
    await api.put(`/employees/${editing.value.user_id}`, editing.value);
    showEditModal.value = false;
    editing.value = null;
    fetchEmployees();
  } catch (err) {
    console.error(err);
  }
};

// Delete
const remove = async (id) => {
  if (!confirm("Delete this employee?")) return;

  try {
    await api.delete(`/employees/${id}`);
    fetchEmployees();
  } catch (err) {
    console.error(err);
  }
};
</script>

<template>
  <div class="mx-4 mb-4">
    <!-- Toggle Create Form -->
    <button
      @click="showCreateModal = true"
      class="bg-blue-500 hover:bg-blue-600 text-white font-bold mt-6 px-4 py-2 rounded-md"
    >
      Add Employee
    </button>

    <!-- Create Employee Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
      <div class="bg-white w-[450px] p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-bold mb-4">Add Employee</h2>

        <div class="space-y-3">
          <div>
            <label class="font-medium">Employee ID</label>
            <input v-model="form.user_id" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Employee Name</label>
            <input v-model="form.user_name" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Gender</label>
            <input v-model="form.gender" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Department ID</label>
            <input v-model="form.department_id" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Join Date</label>
            <input v-model="form.join_date" type="date" class="w-full border px-3 py-2 rounded-md" />
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <button
            @click="showCreateModal = false"
            class="bg-red-100 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-md"
          >
            Cancel
          </button>

          <button
            @click="create"
            :disabled="!isFormValid"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:bg-gray-300"
          >
            Create
          </button>
        </div>
      </div>
    </div>

    <!-- Employee List -->
    <h2 class="text-2xl font-bold uppercase mt-6">Employee List</h2>
    <div class="w-[15%] h-0.5 bg-red-500 mb-6"></div>

    <table class="w-full border-collapse border border-gray-400 bg-white text-sm mt-6">
      <thead class="bg-gray-50">
        <tr class="text-center text-md font-semibold">
          <th class="border p-4">Employee ID</th>
          <th class="border p-4">Employee Name</th>
          <th class="border p-4">Department</th>
          <th class="border p-4">Join Date</th>
          <th class="border p-4">Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="e in employees"
          :key="e.user_id"
          class="odd:bg-white even:bg-gray-50 text-center"
        >
          <td class="border p-2">{{ e.user_id }}</td>
          <td class="border text-left px-4">{{ e.user_name }}</td>
          <td class="border text-left px-4">{{ e.department_name || e.department_id }}</td>
          <td class="border">{{ e.join_date }}</td>
          <td class="border">
            <button
              @click="edit(e)"
              class="bg-yellow-500 hover:bg-yellow-700 text-white font-medium mr-4 my-1 px-4 py-2 rounded-md"
            >
              Edit
            </button>
            <button
              @click="remove(e.user_id)"
              class="bg-red-500 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-md"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Edit Modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black/60 bg-opacity-40 flex items-center justify-center z-50"
    >
      <div class="bg-white w-[450px] p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-bold mb-4">Edit Employee</h2>

        <div class="space-y-3">
          <div>
            <label class="font-medium">Employee Name</label>
            <input v-model="editing.user_name" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Gender</label>
            <input v-model="editing.gender" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Department ID</label>
            <input v-model="editing.department_id" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label class="font-medium">Join Date</label>
            <input
              v-model="editing.join_date"
              type="date"
              class="w-full border px-3 py-2 rounded-md"
            />
          </div>
        </div>

        <div class="flex justify-end mt-6 space-x-3">
          <button
            @click="showEditModal = false"
            class="bg-red-100 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-md"
          >
            Cancel
          </button>

          <button
            @click="update"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
