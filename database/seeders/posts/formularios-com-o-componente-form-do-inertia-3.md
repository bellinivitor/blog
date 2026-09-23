O `useForm` resolveu muita coisa, mas para formulários simples eu escrevia sempre o mesmo código: estado, submit, erros. O componente `Form` do Inertia 3 elimina quase tudo isso.

## O básico

```vue
<Form v-bind="TagController.store.form()" v-slot="{ errors, processing }">
    <input name="name" />
    <p v-if="errors.name">{{ errors.name }}</p>

    <button :disabled="processing">Criar</button>
</Form>
```

Os campos são lidos pelo atributo `name`, como num formulário HTML comum. Com o Wayfinder, a rota e o método vêm tipados.

## Quando ainda uso useForm

- Quando o valor de um campo depende de outro enquanto o usuário digita.
- Quando preciso transformar os dados antes de enviar.
- Em componentes customizados que não renderizam um `input` com `name`.

## Um detalhe que me pegou

Campos com `name="tags[]"` viram array automaticamente. Se nenhum checkbox estiver marcado, o campo simplesmente não é enviado, então o backend precisa tratar a ausência como lista vazia.
