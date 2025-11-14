# Инструкция по загрузке на GitHub

## Быстрая настройка

### 1. Настройте Git (один раз)
```bash
git config --global user.name "Данил Трубицын"
git config --global user.email "your-email@example.com"
```

### 2. Создайте репозиторий на GitHub
- Перейдите на https://github.com/new
- Название: `wc-avito`
- Описание: "WooCommerce Avito XML Generator Plugin"
- Private или Public (на выбор)
- НЕ добавляйте README, .gitignore, license (они уже есть)
- Создайте репозиторий

### 3. Подключите и загрузите код
```bash
# Добавьте удаленный репозиторий (замените YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/wc-avito.git

# Загрузите код
git push -u origin main
```

## Готово!

Ваш плагин теперь на GitHub. Ссылка будет:
`https://github.com/YOUR_USERNAME/wc-avito`

## Дальнейшая работа

### Добавить изменения
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
git log --oneline
```

## Что уже сделано

✅ Git репозиторий инициализирован
✅ Создан .gitignore (исключает .DS_Store, логи, временные файлы)
✅ Создан README.md с полной документацией
✅ Все файлы добавлены и закоммичены
✅ 15 файлов готовы к загрузке (3129+ строк кода)

## Структура проекта

```
wc-avito/
├── .gitignore                  # Исключения для Git
├── README.md                   # Документация
├── GITHUB_UPLOAD.md           # Эта инструкция
├── avito-uralgips.php         # Главный файл плагина
├── uninstall.php              # Деинсталляция
└── includes/                  # Функционал плагина
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
