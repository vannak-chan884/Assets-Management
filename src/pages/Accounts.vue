<template>
  <div>
    <h2 class="text-2xl font-bold uppercase mt-6">Accounts</h2>
    <div class="w-[15%] h-0.5 bg-red-500 mb-6"></div>
    <div>
      <label class="mr-2">Type:</label>
      <select
        v-model="sub"
        class="w-[15%] rounded-md border border-gray-300 mr-2 px-3 py-2 placeholder-gray-400 shadow-sm invalid:border-red-500 invalid:text-red-600 focus:border-sky-500 focus:outline focus:outline-sky-500 focus:invalid:border-red-500 focus:invalid:outline-red-500 disabled:border-gray-200 disabled:bg-gray-50 disabled:shadow-none sm:text-sm"
      >
        <option value="domain">Domain</option>
        <option value="gmail">Gmail</option>
        <option value="erp">ERP</option>
        <option value="bpm">BPM</option>
      </select>
      <button
        @click="load"
        class="w-[5%] bg-blue-500 hover:not-focus:bg-blue-700 text-white font-bold px-4 py-2 rounded-md cursor-pointer"
      >
        Load
      </button>
    </div>
    <div v-if="list.length">
      <table class="w-full border-collapse border border-gray-400 bg-white text-sm mt-6">
        <thead class="bg-gray-50">
          <tr class="text-center text-md font-semibold">
            <th class="border border-gray-300 p-4 font-semibold text-gray-900">UserID</th>
            <th class="border border-gray-300 p-4 font-semibold text-gray-900">Username</th>
            <th class="border border-gray-300 p-4 font-semibold text-gray-900">Password</th>
            <th class="border border-gray-300 p-4 font-semibold text-gray-900">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in list" :key="a.user_id">
            <td class="w-[5%] border border-gray-300 text-gray-500 px-4">{{ a.user_id }}</td>
            <td class="w-[40%] border border-gray-300 text-gray-500 px-4">{{ a.domain_user || a.gmail || a.erp_user || a.bpm_user }}</td>
            <td class="w-[40%] border border-gray-300 text-gray-500 px-4">{{ a.domain_password || a.gmail_password || a.erp_password || a.bpm_password }}</td>
            <td class="w-[15%] border border-gray-300 text-gray-500 px-4">
              <button @click="edit(a)" class="bg-yellow-500 hover:not-focus:bg-yellow-700 text-white font-medium mr-4 my-1 px-4 py-2 rounded-md cursor-pointer">Edit</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import api from "../api";
export default {
  data() {
    return { sub: "domain", list: [] };
  },
  methods: {
    async load() {
      this.list = (await api.get(`/accounts?sub=${this.sub}`)).data;
    },
    edit(a) {
      alert("Open edit dialog (implement as needed)");
    },
  },
};
</script>
