# Инструкция по загрузке на GitHub

## Репозиторий

**URL**: https://github.com/ali3412-lgtm/avito-uralgips

## Быстрые команды

### Загрузить изменения
```bash
git add .
git commit -m "Описание изменений"
git push
```

### Проверить статус
```bash
git status
```

### Посмотреть историю
```bash
git log --oneline -10
```

## Первоначальная настройка (уже выполнена)

✅ Git репозиторий инициализирован  
✅ Remote настроен: `origin -> https://github.com/ali3412-lgtm/avito-uralgips.git`  
✅ Создан .gitignore  
✅ Создан README.md с документацией  

## Структура проекта

```
avito-uralgips/
├── .gitignore
├── README.md
├── GITHUB_UPLOAD.md
├── avito-uralgips.php         # Главный файл плагина
├── uninstall.php
└── includes/
    ├── admin-menu.php
    ├── category-export-field.php
    ├── cron.php
    ├── dynamic-category-fields.php
    ├── dynamic-product-fields.php
    ├── field-manager.php
    ├── field-settings-page.php
    ├── product-export-field.php
    ├── product-fields.php
    ├── xml-generator-dynamic.php
    └── xml-generator.php
```
