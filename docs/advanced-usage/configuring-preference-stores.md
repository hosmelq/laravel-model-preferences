---
title: Configuring preference stores
weight: 1
---

Drivers can be configured globally in `config/model-preferences.php`.

- `column`: uses a JSON column on the model
- `table`: stores preferences in a dedicated table
- `shared`: stores preferences in one shared polymorphic table

### Generate a custom table migration

```bash
php artisan model-preferences:table name_of_the_preferences_table
```

Use one table per model class (`table`) or the shared table (`shared`) based on your
query and cleanup requirements.

