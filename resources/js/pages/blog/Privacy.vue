<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BackToPosts from '@/components/blog/BackToPosts.vue';
import { COOKIE_NOTICE_STORAGE_KEY } from '@/components/blog/CookieNotice.vue';

defineProps<{
    sessionCookie: string;
}>();

const blog = computed(() => usePage().props.blog);
</script>

<template>
    <Head title="Privacidade e cookies" />

    <section class="max-w-[68ch] pb-16">
        <BackToPosts />
        <h1
            class="mt-4 font-[family-name:var(--font-title)] text-[2rem] leading-tight font-bold tracking-[-0.02em]"
        >
            Privacidade e cookies
        </h1>
        <p class="mt-4 text-[var(--graphite)]">
            Este blog não tem anúncios, rastreadores nem ferramentas de
            analytics de terceiros. Ele guarda no seu navegador só o necessário
            para funcionar.
        </p>
    </section>

    <div class="blog-prose max-w-[68ch]">
        <h2>Cookies essenciais</h2>
        <p>
            São usados em toda visita e não dá para desligá-los sem quebrar o
            site. Pela LGPD, eles não dependem de consentimento.
        </p>
        <ul>
            <li>
                <code>{{ sessionCookie }}</code
                >: identifica a sessão no servidor. Expira quando você fica um
                tempo sem acessar o blog.
            </li>
            <li>
                <code>XSRF-TOKEN</code>: protege os formulários contra envios
                forjados por outros sites (CSRF).
            </li>
        </ul>

        <h2>Preferências</h2>
        <p>Só são gravadas quando você mesmo escolhe algo:</p>
        <ul>
            <li>
                <code>appearance</code> (cookie e armazenamento local): o tema
                claro, escuro ou do sistema, se você trocar no botão do topo.
                Dura um ano.
            </li>
            <li>
                <code>{{ COOKIE_NOTICE_STORAGE_KEY }}</code> (armazenamento
                local): lembra que você já viu o aviso sobre cookies.
            </li>
        </ul>

        <h2>O que não é coletado</h2>
        <p>
            O blog conta quantas vezes cada post foi lido em cada dia. Para não
            contar a mesma pessoa duas vezes no mesmo dia, o servidor mistura
            seu IP e seu navegador com um código aleatório que é descartado à
            meia-noite, e guarda só o resultado até o fim do dia. O IP em si
            nunca é gravado, e depois da meia-noite não há como voltar a ele nem
            ligar visitas de dias diferentes. Isso é feito por legítimo
            interesse (estatística do próprio blog), sem cookie. Não há login
            para leitores, comentários ou newsletter.
        </p>

        <h2>Contato</h2>
        <p>
            Dúvidas sobre seus dados? Fale com {{ blog.author }}:
            <template v-for="(link, index) in blog.links" :key="link.url">
                <a
                    :href="link.url"
                    class="blog-link"
                    rel="noopener"
                    target="_blank"
                    >{{ link.label }}</a
                >{{ index < blog.links.length - 1 ? ' ou ' : '.' }}
            </template>
        </p>
    </div>
</template>
