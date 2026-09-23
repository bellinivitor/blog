O pior dark mode é o que pisca: a página abre clara e meio segundo depois escurece.

## A causa

O tema é decidido no JavaScript, que roda depois do primeiro pintar da tela.

## A solução em três partes

1. Um cookie guarda a preferência (`light`, `dark` ou `system`).
2. O servidor lê o cookie e já manda a classe `dark` no `<html>`.
3. Um script inline mínimo no `<head>` resolve o caso `system` antes do CSS aplicar.

```html
<script>
    const theme = '{{ $appearance }}';
    if (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.classList.add('dark');
    }
</script>
```

## No Tailwind 4

A variante `dark:` passa a depender da classe com uma linha no CSS:

```css
@custom-variant dark (&:is(.dark *));
```

Pronto: nenhuma tela branca antes da escura.
