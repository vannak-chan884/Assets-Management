<template>
  <div class="relative mx-4 mb-4">
    <div class="sticky top-18 bg-white">
      <h2 class="text-2xl font-bold uppercase mt-6">Assignment Form</h2>
      <div class="w-[15%] h-0.5 bg-red-500 mb-6"></div>
      <div style="margin-bottom: 12px">
        <select
          v-model="assign.user_id"
          class="w-[15%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
        >
          <option v-for="e in employees" :value="e.user_id">
            {{ e.user_name }} ({{ e.user_id }})
          </option>
        </select>
        <select
          v-model="assign.device_id"
          class="w-[20%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
        >
          <option v-for="d in devices" :value="d.device_id">
            {{ d.device_name }} ({{ d.device_id }})
          </option>
        </select>
        <input
          v-model="assign.note"
          class="w-[40%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
          placeholder="Note"
        />
        <button
          @click="assignDevice"
          class="w-[10%] bg-blue-500 hover:not-focus:bg-blue-700 text-white font-bold px-4 py-2 rounded-md cursor-pointer"
        >
          Assign
        </button>
      </div>
    </div>

    <h2 class="text-2xl font-bold uppercase mt-6">Device Assignment List</h2>
    <div class="w-[18%] h-0.5 bg-red-500 mb-6"></div>
    <table class="w-full border-collapse border border-gray-400 bg-white text-sm mt-6">
      <thead class="bg-gray-50">
        <tr class="text-center text-md font-semibold">
          <!-- <th class="border border-gray-300 p-4 font-semibold text-gray-900">ID</th> -->
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Property Number</th>
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Device Name</th>
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Employee ID</th>
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Employee Name</th>
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Assigned At</th>
          <th class="border border-gray-300 p-4 font-semibold text-gray-900">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="a in assignments"
          :key="a.assignment_id"
          class="odd:bg-white even:bg-gray-50 text-center"
        >
          <!-- <td class="w-[3%] border border-gray-300 text-gray-500">{{ a.assignment_id }}</td> -->
          <td class="w-[10%] border border-gray-300 text-left px-4 text-gray-500">
            {{ a.device_id }}
          </td>
          <td class="w-[10%] border border-gray-300 text-left px-4 text-gray-500">
            {{ a.device_name }}
          </td>
          <td class="w-[5%] border border-gray-300 text-left px-4 text-gray-500">
            {{ a.user_id }}
          </td>
          <td class="w-[10%] border border-gray-300 text-left px-4 text-gray-500">
            {{ a.user_name }}
          </td>
          <td class="w-[20%] border border-gray-300 text-left px-4 text-gray-500">
            {{ a.assigned_at }}
          </td>
          <td class="w-[10%] border border-gray-300 text-gray-500">
            <button
              @click="edit(a)"
              class="bg-yellow-500 hover:not-focus:bg-yellow-700 text-white font-medium mr-4 my-1 px-4 py-2 rounded-md cursor-pointer"
            >
              Edit
            </button>
            <button
              @click="remove(a.assignment_id)"
              class="bg-red-500 hover:not-focus:bg-red-700 text-white font-medium my-1 px-4 py-2 rounded-md cursor-pointer"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="editing" class="my-6">
      <h3 class="mb-4">
        Reassignment for:
        <span class="text-yellow-500 text-xl font-bold"
          >{{ editing.user_name }} ({{ editing.user_id }})</span
        >
      </h3>
      <!-- <input v-model="editing.user_id" /> -->
      <input
        v-model="editing.device_id"
        class="w-[10%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
      />
      <!-- <input v-model="editing.device_name" /> -->
      <input
        v-model="editing.assigned_at"
        type="date"
        class="w-[15%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
      />
      <input
        v-model="editing.note"
        class="w-[25%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
      />
      <button
        @click="update()"
        class="w-[10%] bg-blue-500 hover:not-focus:bg-blue-700 text-white font-bold px-4 py-2 rounded-md cursor-pointer"
      >
        Save
      </button>
    </div>
  </div>
</template>

<script>
import api from "../api";
export default {
  data() {
    return {
      assignments: [],
      employees: [],
      devices: [],
      assign: {
        assignment_id: "",
        user_id: "",
        device_id: "",
        assigned_at: "",
        released_at: "",
        note: "",
      },
      editing: null,
    };
  },
  mounted() {
    this.load();
  },
  methods: {
    async load() {
      this.assignments = (await api.get("/assignments")).data;
      this.employees = (await api.get("/employees")).data;
      this.devices = (await api.get("/devices")).data;
    },
    async assignDevice() {
      await api.post("/assignments", this.assign);
      this.assign = { user_id: "", device_id: "", note: "" };
      this.load();
    },
    edit(a) {
      this.editing = { ...a };
    },
    async update() {
      await api.put(`/assignments/${this.editing.assignment_id}`, this.editing);
      this.editing = null;
      this.fetch();
    },
    async remove(id) {
      if (!confirm("Delete?")) return;
      await api.delete(`/assignments/${id}`);
      this.fetch();
    },
  },
};
</script>
