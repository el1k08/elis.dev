<script setup lang="ts">
import { ref } from 'vue'
import { Dialog, DialogPanel } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const navigation = [
  { name: 'Work I\'ve Done', subname:'portfolio', href: '#' },
  { name: 'The Story', subname:'about us', href: '#' },
  { name: 'Ping Me', subname:'contact us', href: '#' },
]

const mobileMenuOpen = ref(false)
</script>
<template>
  <div class="bg-white dark:bg-black">
    <header class="absolute inset-x-0 top-0 z-50">
      <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
        <div class="flex lg:flex-1">
            <a href="#" class="-m-1.5 p-1.5">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">ELIS.DEV</span>
            </a>
        </div>
        <div class="flex lg:hidden">
          <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700 dark:text-gray-200" @click="mobileMenuOpen = true">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon class="size-6" aria-hidden="true" />
          </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
          <a v-for="item in navigation" :key="item.name" :href="item.href" class="flex flex-col text-center">
            <span class="text-base font-semibold text-gray-900 dark:text-white">{{ item.name }}</span>
            <span class="text-sm/6 text-gray-900/50 dark:text-white/50 line-through">{{ item.subname }}</span>
          </a>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
          <a href="login" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Log in <span aria-hidden="true">&rarr;</span></a>
        </div>
      </nav>
      <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
        <div class="fixed inset-0 z-50"></div>
        <DialogPanel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10 dark:bg-black dark:sm:ring-gray-100/10">
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">ELIS.DEV</span>
            </a>
            <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700 dark:text-gray-200" @click="mobileMenuOpen = false">
              <span class="sr-only">Close menu</span>
              <XMarkIcon class="size-6" aria-hidden="true" />
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-gray-500/10 dark:divide-white/10">
              <div class="space-y-2 py-6">
                <a v-for="item in navigation" :key="item.name" :href="item.href" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5 flex flex-col">
                    <span>{{ item.name }}</span>
                    <span class="text-sm/6 text-gray-900/50 dark:text-white/50 line-through">{{ item.subname }}</span>
                </a>
              </div>
              <div class="py-6">
                <a href="login" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Log in</a>
              </div>
            </div>
          </div>
        </DialogPanel>
      </Dialog>
    </header>

    <div class="relative isolate px-6 pt-14 lg:px-8">
    <div class="hidden lg:block absolute right-0 bottom-0 w-52 h-52 lg:w-[512px] lg:h-[512px] pointer-events-none">
        <img src="/images/granny.png" alt="" class="w-full h-full object-contain" />
    </div>
    <div class="mx-auto max-w-4xl py-32 sm:py-48 lg:py-56 relative">

      <div class="text-center">
        <h1 class="text-xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl dark:text-white">Granny's rules. Deal with it.</h1>
        <h1 class="text-xl font-semibold tracking-tight text-balance text-gray-900/40 sm:text-7xl dark:text-white/40">Grow your business.</h1>

        <p class="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8 dark:text-gray-400">Smart Hustle Studio</p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
        <a href="#" class="rounded-md bg-amber-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-amber-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400 dark:focus-visible:outline-amber-500">Granny's Hot Takes</a>
        </div>
        <p class="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8 dark:text-gray-400">'Number One' is overcrowded. Be real instead.</p>
      </div>
    </div>
    </div>
  </div>
</template>
