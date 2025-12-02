<template>
  <div class="relative">
    <nav class="sticky top-0 w-full p-4 bg-white right-0 left-0 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-white font-medium z-20">
      <router-link to="/" class="bg-red-700 hover:bg-red-400 px-4 py-2 rounded-lg">Employees</router-link>
      <router-link to="/devices" class="bg-red-700 hover:bg-red-400 px-4 py-2 rounded-lg">Devices</router-link>
      <router-link to="/assignments" class="bg-red-700 hover:bg-red-400 px-4 py-2 rounded-lg">Assignments</router-link>
      <router-link to="/accounts" class="bg-red-700 hover:bg-red-400 px-4 py-2 rounded-lg">Accounts</router-link>
    </nav>
    <main>
      <router-view />
    </main>
  </div>
</template>
