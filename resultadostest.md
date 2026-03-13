# Resultados de Testing

## Metadata
- Fecha: 2026-03-07
- Entorno: LocalWP Linux, sitio local `http://localhost:10008`
- WP version: 6.9.1
- PHP version: 8.4.18
- DB mode (TCP/socket): `socket` (`/home/gian/.config/Local/run/CpFd9Hqf4/mysql/mysqld.sock`)
- Version plugin: 1.0.9
- Commit/revision: `df2cea69de54184453ccc209121b2372fa30270f` con working tree local dirty
- Ejecutado por: OpenCode

## Pre-check
- Dependencias OK/Faltantes: `php`, `composer`, `node`, `npm`, `mysql`, `mysqladmin`, `curl`, `svn`, `wp`, `python3` presentes. Tooling de proyecto aun no instalado al iniciar esta corrida.
- Observaciones: El plugin no usa REST API ni Playwright en este plan. Se adopta Perfil A adaptado con foco en PHPUnit, WPCS, PHPStan y Semgrep.

## Toolchain verificado
- `php -v`: `PHP 8.4.18`
- `composer --version`: `Composer 2.8.3`
- `node -v`: `v22.22.1`
- `npm -v`: `10.9.4`
- `wp --version`: `WP-CLI 2.12.0`
- `python3 --version`: `Python 3.12.3`
- `mysql --version`: `8.0.45`
- `vendor/bin/phpunit --version`: `PHPUnit 9.6.34`
- `vendor/bin/phpcs --version`: `PHPCS 3.13.5`
- `vendor/bin/phpstan --version`: `PHPStan 1.12.33`
- `./.venv-tools/bin/semgrep --version`: `1.154.0`

## Ejecucion
- Perfil ejecutado: `A` adaptado (backend/admin-ajax/datos)
- Comandos usados:
  - `composer test:list`
  - `composer test`
  - `composer qa`
  - `composer plugin:check`
  - `composer release:gate:a`
  - `composer audit --no-interaction`
- Baseline local (tiempo/queries) usado para comparacion: No definido aun
- Baseline versionado (si aplica): `NO APLICA` por ahora
- Auditoria de dependencias: `composer audit --no-interaction` sin vulnerabilidades reportadas.
- Checks `NO APLICA` por perfil: Playwright/E2E deshabilitado por decision explicita del proyecto; no hay endpoint de descarga protegida por token ni flujo REST browser-first.
- Total tests: 13
- Passed: 13
- Failed: 0
- Warnings: 0 en PHPUnit. QA estatico verde. Plugin Check con warnings no bloqueantes del repo/documentacion y falso positivo de nonce centralizado.

## Suite ejecutada
- `EasyRolesManagerTest`
  - `test_create_role_tracks_custom_role_and_capabilities`
  - `test_create_role_rejects_duplicate_slug`
  - `test_clone_role_copies_capabilities`
  - `test_delete_role_blocks_protected_roles`
  - `test_delete_role_reassigns_existing_users_to_subscriber`
  - `test_changelog_is_capped_to_latest_hundred_entries`
- `EasyRolesAjaxTest`
  - `test_create_role_requires_manage_options`
  - `test_create_role_rejects_invalid_nonce`
  - `test_export_roles_returns_expected_payload_shape`
  - `test_change_user_role_blocks_self_demote`
  - `test_import_roles_rejects_invalid_json_payload`
- `EasyRolesLifecycleTest`
  - `test_activation_initializes_plugin_options`
  - `test_uninstall_removes_plugin_options_meta_and_custom_roles`

## Evidencia de ejecucion
- `composer test:list`: 13 tests detectados, sin `No tests executed!`
- `composer test`: `OK (13 tests, 42 assertions)`
- `composer qa`: `PHPCS` verde, `PHPStan` verde, `Semgrep` verde
- `composer plugin:check`: sin errores bloqueantes del plugin tras ajustes de metadata; quedan warnings documentados del repo y del patron centralizado de nonce
- `composer release:gate:a`: ejecutado correctamente con estado final exitoso
- `composer audit --no-interaction`: sin advisories
- DB de tests confirmada: `easy_roles_gb_tests` creada y usada sobre socket LocalWP

## Cobertura por alcance (1-27)
- Alcance 1: `PASS` parcial de primera capa. Cubierto con authz sobre `admin-ajax` para rol sin privilegios y nonce invalido.
- Alcance 2: `PASS` parcial de primera capa. Cubierto con import invalido y validacion de slugs/duplicados/caps basicos.
- Alcance 3: `PASS` parcial. Cubierto export/import JSON y shape del payload; no aplica token de descarga.
- Alcance 4: `PASS` parcial. Cubierto contrato JSON de handlers testeados.
- Alcance 5: `NO APLICA`. No hay migraciones legacy implementadas.
- Alcance 6: `PASS` parcial. Cubierto changelog y truncado a 100 entradas.
- Alcance 7: `PARCIAL`. Infra lista, pero sin baseline ni smoke de performance versionado.
- Alcance 8: `PASS` parcial. Los handlers probados devuelven errores controlados sin fatales.
- Alcance 9: `PARCIAL`. Hay cobertura indirecta en flujos create/delete/import, pero no reintentos dedicados.
- Alcance 10: `NO APLICA`. No existe endpoint de descarga con headers propios.
- Alcance 11: `PASS` parcial. Cubierto nonce invalido y control de capacidad en AJAX.
- Alcance 12: `PARCIAL`. Falta suite de payload extremo grande/complejo.
- Alcance 13: `PASS` parcial. Cubierto activate/uninstall; deactivate no requiere accion destructiva.
- Alcance 14: `PARCIAL`. Riesgo identificado, sin test concurrente dedicado aun.
- Alcance 15: `NO APLICA`. Sin pagos.
- Alcance 16: `NO APLICA`. Sin pedidos/pagos.
- Alcance 17: `NO APLICA`. Sin webhooks.
- Alcance 18: `NO APLICA`. Sin montos/impuestos.
- Alcance 19: `NO APLICA`. Sin estados de pedido.
- Alcance 20: `NO APLICA`. Sin PCI/PII especifica de pagos.
- Alcance 21: `PARCIAL`. Gap documentado; no existe logging dedicado de bloqueos.
- Alcance 22: `NO APLICA`. No hay tokens temporales ni artefactos expirables.
- Alcance 23: `PASS` parcial. Cubierto defaults seguros y options base en activacion.
- Alcance 24: `NO APLICA`. Sin migraciones legacy.
- Alcance 25: `PASS` parcial. Cubierto contrato del cliente `admin-ajax` en handlers priorizados.
- Alcance 26: `PASS` parcial. Cubierta parte de la matriz de roles/capacidades con administrator vs subscriber.
- Alcance 27: `NO APLICA`. WooCommerce solo aporta capabilities, no operaciones de negocio.

## Aplicabilidad por alcance (1-27)

| Alcance | Estado (Aplica/No aplica) | Motivo (si No aplica) | Evidencia |
|---|---|---|---|
| 1 | Aplica |  | `includes/class-easy-roles-ajax.php` |
| 2 | Aplica |  | `includes/class-easy-roles-ajax.php` |
| 3 | Aplica parcial |  | `includes/class-easy-roles-ajax.php:313` |
| 4 | Aplica |  | `includes/class-easy-roles-ajax.php` |
| 5 | No aplica | No hay capa de migracion ni compat legacy declarada | `easy-roles-gb.php` |
| 6 | Aplica |  | `includes/class-easy-roles-manager.php:289` |
| 7 | Aplica parcial |  | `includes/class-easy-roles-manager.php:269` |
| 8 | Aplica |  | `includes/class-easy-roles-ajax.php` |
| 9 | Aplica |  | `includes/class-easy-roles-manager.php` |
| 10 | No aplica | No existe descarga protegida con headers propios | `assets/js/easy-roles-admin.js:1588` |
| 11 | Aplica |  | `includes/class-easy-roles-ajax.php:45` |
| 12 | Aplica |  | `includes/class-easy-roles-ajax.php:351` |
| 13 | Aplica |  | `easy-roles-gb.php:54`, `uninstall.php` |
| 14 | Aplica parcial |  | `includes/class-easy-roles-manager.php:210` |
| 15 | No aplica | Plugin sin pagos | `DOCUMENTATION.md` |
| 16 | No aplica | Plugin sin pedidos/pagos | `DOCUMENTATION.md` |
| 17 | No aplica | Plugin sin webhooks | `DOCUMENTATION.md` |
| 18 | No aplica | Plugin sin montos/impuestos | `DOCUMENTATION.md` |
| 19 | No aplica | Plugin sin pedidos | `DOCUMENTATION.md` |
| 20 | No aplica | Sin dominio PCI/PII de pagos | `DOCUMENTATION.md` |
| 21 | Aplica parcial | No hay logging de denegaciones todavia | `includes/class-easy-roles-ajax.php:45` |
| 22 | No aplica | No hay tokens temporales ni cleanup de artefactos | `includes/class-easy-roles-ajax.php` |
| 23 | Aplica |  | `easy-roles-gb.php:57` |
| 24 | No aplica | Sin migraciones legacy | `easy-roles-gb.php` |
| 25 | Aplica |  | `includes/class-easy-roles-ajax.php` |
| 26 | Aplica |  | `includes/class-easy-roles-manager.php` |
| 27 | No aplica | WooCommerce solo integra caps y roles protegidos | `includes/class-easy-roles-woocommerce.php` |

## Hallazgos
- ID: ER-TEST-001
- Severidad: Medium
- Evidencia: `wp plugin check easy-roles-gb` sigue marcando warnings por markdowns adicionales en la raiz y por el patron de verificacion centralizada de nonce en `includes/class-easy-roles-ajax.php`.
- Estado: Abierto, documentado.

- ID: ER-TEST-002
- Severidad: Medium
- Evidencia: La primera pasada de testing cubre manager, AJAX y lifecycle, pero aun no cubre concurrencia real, payloads extremos complejos ni el bug conocido de actualizacion de roles protegidos.
- Estado: Abierto, backlog de siguientes tests.

- ID: ER-TEST-003
- Severidad: Low
- Evidencia: `PHPStan 2.x` aparece disponible, pero la base actual queda estabilizada en `1.12.33` para no meter otra variable de migracion en esta pasada.
- Estado: Aceptado temporalmente.

## Proximo backlog recomendado
- Agregar test que reproduzca el bug de actualizacion de roles protegidos (`administrator` / roles Woo protegidos).
- Agregar test de borrado por lotes con varios usuarios para comprobar si el `offset` salta registros.
- Agregar tests de payload extremo para `import_data`, slugs largos y matrices grandes de capabilities.
- Agregar smoke de contrato para `compare_roles`, `get_users` y `get_changelog`.
- Separar warnings de `plugin-check` entre ruido del repo y problemas reales de release package.

## Riesgos aceptados / fuera de alcance
- Item: Playwright y E2E browser
- Justificacion: El proyecto no cuenta con Playwright en este plan y el usuario pidio excluirlo.
- Plan futuro: Evaluar solo si luego se requiere smoke UI real.

- Item: Plugin Check con warnings de estructura de repo
- Justificacion: Este repositorio contiene documentacion tecnica y archivos auxiliares que no son parte de un zip final de distribucion para WordPress.org.
- Plan futuro: excluirlos del paquete de release o mover documentacion a una carpeta no distribuida.

## Conclusion
- Resultado final: PASS con riesgos documentados
- Cierre de esta pasada: infraestructura lista, suite base ejecutable y QA estatico operativo. La siguiente pasada debe enfocarse en bugs conocidos y alcances `PARCIAL`.

---

## Metadata
- Fecha: 2026-03-13
- Entorno: LocalWP Linux, WordPress Test Suite persistente en `~/.local/share/wordpress-tests-lib`
- WP version: 6.9.1
- PHP version: 8.4.18
- DB mode (TCP/socket): `socket`
- Version plugin: 1.0.9
- Commit/revision: working tree local dirty
- Ejecutado por: OpenCode

## Pre-check
- Dependencias OK/Faltantes: `vendor/bin/phpunit`, `vendor/bin/phpcs`, `vendor/bin/phpstan`, `./.venv-tools/bin/semgrep` y `wp` disponibles.
- Observaciones: Para esta corrida PHPUnit se exporto `WP_TESTS_DIR=/home/gian/.local/share/wordpress-tests-lib` porque `tests/bootstrap.php` sigue cayendo a `/tmp/wordpress-tests-lib` si la variable no esta definida.

## Ejecucion
- Perfil ejecutado: `A` adaptado (backend/admin-ajax/datos)
- Comandos usados:
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesManagerTest.php`
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesAjaxTest.php`
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesLifecycleTest.php`
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesCapabilitiesTest.php`
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesWooCommerceTest.php`
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesAdminTest.php`
  - `composer test`
  - `composer phpcs`
  - `composer phpstan`
  - `composer semgrep`
  - `composer plugin:check`
- Baseline local (tiempo/queries) usado para comparacion: `NO APLICA`
- Baseline versionado (si aplica): `NO APLICA`
- Perfil de dataset usado: `medium`
- Dataset sintetico usado: usuarios admin/subscriber, roles custom, opciones del plugin y capabilities custom/WooCommerce para cubrir manager, AJAX, uninstall y admin bootstrap.
- Auditoria de dependencias: no re-ejecutada en esta pasada.
- Checks `NO APLICA` por perfil: E2E REST puro, tokens single-use de descarga y alcances de pagos/webhooks.
- Total tests: 69
- Passed: 69
- Failed: 0
- Warnings: 0 en PHPUnit; `plugin:check` mantiene warnings no bloqueantes preexistentes del repo/documentacion y del patron de nonce centralizado.

## Cobertura por alcance (1-27)
- Alcance 1: `PASS` mas fuerte. Se amplio la matriz de authz/capability sobre `admin-ajax` para handlers mutables y de lectura.
- Alcance 2: `PASS` mas fuerte. Se agregaron casos de campos vacios, slugs largos, entradas invalidas y mezcla de payloads validos/invalidos en import.
- Alcance 3: `PASS` parcial. Export/import JSON cubiertos; sigue sin aplicar descarga protegida con token.
- Alcance 4: `PASS`. Se cubrio mejor el contrato JSON de exito/error en handlers AJAX clave.
- Alcance 5: `NO APLICA`. No hay migraciones legacy implementadas.
- Alcance 6: `PASS`. Changelog cubierto en truncado, count y paginacion AJAX.
- Alcance 7: `PARCIAL`. Sin baseline de performance ni smoke de carga.
- Alcance 8: `PASS`. Errores controlados en manager y AJAX ampliados sin fatales.
- Alcance 9: `PASS` parcial. Mejoro la cobertura de idempotencia/lados del estado, aunque no hay test concurrente dedicado.
- Alcance 10: `NO APLICA`. No existe endpoint de descarga con headers propios.
- Alcance 11: `PASS`. Sigue cubierta la capa nonce/capability en AJAX.
- Alcance 12: `PARCIAL`. Aun faltan payloads extremos grandes y matrices mas pesadas.
- Alcance 13: `PASS`. Se cubrieron activation, deactivation y uninstall con mas precision.
- Alcance 14: `PARCIAL`. Sin test de concurrencia/race dedicado.
- Alcance 15: `NO APLICA`. Sin pagos.
- Alcance 16: `NO APLICA`. Sin pedidos/pagos.
- Alcance 17: `NO APLICA`. Sin webhooks.
- Alcance 18: `NO APLICA`. Sin montos/impuestos.
- Alcance 19: `NO APLICA`. Sin estados de pedido.
- Alcance 20: `NO APLICA`. Sin dominio PCI/PII de pagos.
- Alcance 21: `PARCIAL`. No existe logging dedicado de bloqueos denegados.
- Alcance 22: `NO APLICA`. No hay tokens temporales ni cleanup de artefactos.
- Alcance 23: `PASS`. Defaults y bootstrap/admin setup cubiertos mejor.
- Alcance 24: `NO APLICA`. Sin migraciones legacy.
- Alcance 25: `PASS`. Contrato del cliente `admin-ajax` mejor cubierto.
- Alcance 26: `PASS` mas fuerte. Se amplio la matriz sobre administrator/subscriber y roles custom.
- Alcance 27: `NO APLICA`. WooCommerce solo integra capabilities/roles protegidos, no operaciones de negocio.

## Hallazgos
- ID: ER-TEST-004
- Severidad: Medium
- Evidencia: `tests/bootstrap.php` depende de `WP_TESTS_DIR` o cae a `/tmp/wordpress-tests-lib`; en esta maquina solo funciona si la variable esta exportada a la ruta persistente.
- Estado: Abierto, recomendado ajustar autodeteccion del bootstrap.

- ID: ER-TEST-005
- Severidad: Low
- Evidencia: `composer plugin:check` sigue reportando warnings de markdowns en raiz, `.claude`, `.gitignore`, variables en `uninstall.php` y el falso positivo de nonce centralizado en `includes/class-easy-roles-ajax.php`.
- Estado: Abierto, no bloqueante para esta pasada de tests.

## Conclusion
- Resultado final: PASS con riesgos documentados
- Cierre de esta pasada: se amplio la suite a `69` tests con cobertura nueva para manager, AJAX, lifecycle, capabilities, WooCommerce y admin bootstrap; PHPCS, PHPStan y Semgrep quedaron en verde.

---

## Metadata
- Fecha: 2026-03-13
- Entorno: LocalWP Linux, WordPress Test Suite persistente en `~/.local/share/wordpress-tests-lib`
- WP version: 6.9.1
- PHP version: 8.4.18
- DB mode (TCP/socket): `socket`
- Version plugin: 1.0.9
- Commit/revision: working tree local dirty
- Ejecutado por: OpenCode

## Pre-check
- Dependencias OK/Faltantes: `vendor/bin/phpunit`, `vendor/bin/phpcs` y `vendor/bin/phpstan` disponibles.
- Observaciones: Se corrigio `tests/bootstrap.php` para autodetectar `WP_TESTS_DIR` en path persistente del usuario antes de caer a `/tmp`.

## Ejecucion
- Perfil ejecutado: `A` adaptado (backend/admin-ajax/datos)
- Comandos usados:
  - `vendor/bin/phpunit --bootstrap tests/bootstrap.php --test-suffix Test.php tests/unit/EasyRolesManagerTest.php`
  - `composer test`
  - `composer phpcs`
  - `composer phpstan`
- Baseline local (tiempo/queries) usado para comparacion: `NO APLICA`
- Baseline versionado (si aplica): `NO APLICA`
- Perfil de dataset usado: `medium`
- Dataset sintetico usado: rol custom con 205 usuarios para reasignacion por lotes, role core protegido `administrator`, y entorno de bootstrap sin `WP_TESTS_DIR` exportado.
- Auditoria de dependencias: no re-ejecutada en esta pasada.
- Checks `NO APLICA` por perfil: E2E REST puro, tokens single-use de descarga y alcances de pagos/webhooks.
- Total tests: 71
- Passed: 71
- Failed: 0
- Warnings: 0 en PHPUnit; PHPCS y PHPStan en verde.

## Hallazgos
- ID: ER-TEST-006
- Severidad: Medium
- Evidencia: `Easy_Roles_Manager::update_role()` permitia mutar roles protegidos y `delete_role()` podia saltarse usuarios en lotes mayores a 100 por el uso de `offset` sobre un conjunto que cambiaba durante la iteracion.
- Estado: Cerrado en esta pasada con tests de regresion y ajuste de implementacion.

- ID: ER-TEST-007
- Severidad: Low
- Evidencia: `tests/bootstrap.php` antes dependia de exportar manualmente `WP_TESTS_DIR` aunque la suite existia en `~/.local/share/wordpress-tests-lib`.
- Estado: Cerrado en esta pasada con autodeteccion local persistente.

## Conclusion
- Resultado final: PASS con riesgos documentados
- Cierre de esta pasada: la suite subio a `71` tests con regresiones reales para roles protegidos, reasignacion por lotes y bootstrap portable; `composer test`, `composer phpcs` y `composer phpstan` quedaron en verde.
