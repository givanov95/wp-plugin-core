# wp-plugin-core — PHP toolkit за изграждане на WordPress плъгини

PHP 8.3+ library (Composer package): service providers, Vite asset integration, REST helpers, admin menus, validation, тънък DB слой. Тестове с PHPUnit 11. Комуникация с потребителя: български. Код, commit-и и PR-и: английски.

Работният флоу (issue-та, PR-и) идва от плъгина `gws@claude-flow` — `/gws:issue <N>`. Този файл носи само спецификите на проекта.

## Branch-ове
- Базов branch: `main`. Issue branch-ове: `fix|feat|chore/N-kratko-ime` от него, PR към него, squash merge.
- Issue-то се затваря с `Fixes #N` в тялото на commit-а (базовият branch е default — затваря се при merge на PR-а).

## Deploy
- Няма — проектът не се качва на сървър. `/gws:ship` не е приложим тук; доставката е merge в базовия branch.

## Build и commit-и
- Няма build стъпка (чист PHP library пакет, без package.json).
- Тестове: `vendor/bin/phpunit` (виж `phpunit.xml`).
- Pre-commit hook от `givanov95/laravel-git-hooks`: php-cs-fixer, debug-statement guard, тестове. Skip при нужда: `SKIP_HOOK=1 git commit ...`.
- Commit стил: Conventional Commits на английски (`fix(scope): ...`).

## GitHub
- Нови issue-та се добавят в project board „gws“.
