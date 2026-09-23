Desde o PHP 8.1 os enums existem, e desde então eu quase não uso constantes de classe para representar estados.

## Backed enums no banco

Com um cast no model, o valor do banco vira o enum automaticamente:

```php
enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
        };
    }
}
```

## Validação sem lista duplicada

Na validação, `Rule::enum(PostStatus::class)` garante que só entram valores válidos, sem repetir a lista em outro lugar.

## Quando o enum não basta

Se trocar de status dispara efeitos, valida permissões ou depende do estado atual, o enum sozinho não dá conta. Aí entra o State pattern: o enum continua sendo o dado, e cada estado decide para onde a transição pode ir.
