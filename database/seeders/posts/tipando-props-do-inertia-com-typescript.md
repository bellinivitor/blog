Rascunho: como mantenho as props das páginas Inertia tipadas sem duplicar tudo à mão.

## O problema

O backend muda um campo do Resource e o front só descobre em produção.

## O que tenho testado

```ts
export type PublishedPost = {
    title: string;
    slug: string;
    published_at: string;
};

defineProps<{ posts: PublishedPost[] }>();
```

Falta decidir se vale gerar esses tipos a partir dos Resources.
