Nem tudo precisa ir para a fila. Este é um rascunho sobre quando eu uso e quando evito.

## Vale a pena

- Envio de e-mail e notificações.
- Chamadas a APIs externas lentas.
- Processamento de imagem.

## Evito

- Escritas que o usuário precisa ver na próxima tela.
- Qualquer coisa que dependa da ordem exata de execução.

```php
SendWelcomeEmail::dispatch($user)->afterCommit();
```
