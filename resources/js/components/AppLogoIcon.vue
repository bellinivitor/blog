<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- Same mark as public/favicon.svg: a Monaspace Xenon V on a dark square. -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        aria-hidden="true"
        :class="className"
        v-bind="$attrs"
    >
        <rect width="32" height="32" rx="7" fill="#262626" />
        <path
            d="M14.56 25h2.93l4.27-15.67 1.37-.64V7h-5.27v1.69l1.28.59-2.6 10.67h-.08l-2.54-10.8 1.01-.46V7H8.87v1.69l1.36.63Z"
            fill="#f7f2e6"
        />
    </svg>
</template>
