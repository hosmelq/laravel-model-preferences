---
title: "Model Deletion"
description: "How stored preferences are cleaned up when models are deleted or soft deleted."
weight: 3
---

Deleting a model automatically removes its stored preferences.

- Models using `SoftDeletes` keep their preferences when soft deleted and remove them when force deleted (`forceDelete()`).
- Mass deletes (`User::query()->delete()`) do not fire Eloquent model events. Tables created with `php artisan model-preferences:table` remove orphaned rows automatically via their cascading foreign key.
