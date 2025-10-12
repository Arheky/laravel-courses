<template>
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/70 backdrop-blur supports-[backdrop-filter]:bg-white/60 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="/" class="font-bold text-lg">{{ appName }}</a>
                    <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">Inertia</span>
            </div>

            <nav class="flex items-center gap-4 text-sm">
                <a href="/" class="hover:underline">Anasayfa</a>
                <template v-if="user">
                    <a v-if="isStudent" href="/courses" class="hover:underline">Kurslar</a>
                    <a v-if="isStudent" href="/my-courses" class="hover:underline">Kayıtlarım</a>
                    <a v-if="isAdmin" href="/admin/dashboard" class="hover:underline">Admin</a>
                    <a href="/profile" class="hover:underline">Profil</a>
                    <form method="POST" action="/logout">
                        <input type="hidden" name="_token" :value="csrf" />
                        <button type="submit" class="hover:underline">Çıkış</button>
                    </form>
            </template>
            <template v-else>
                <a href="/login" class="hover:underline">Giriş</a>
                <a href="/register" class="hover:underline">Kayıt Ol</a>
            </template>
            <button @click="$toggleTheme" class="ml-2 text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-700">Tema</button>
            </nav>
        </div>
    </header>
</template>


<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const isStudent = computed(() => user.value?.role === 'student');
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const appName = computed(() => page.props.appName || 'Laravel');
</script>