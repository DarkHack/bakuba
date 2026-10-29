# Bakuba

Noyau serveur PHP autonome et multiplateforme (Unix / Windows), orienté
événements — la logique de pooling y est convertie en boucle d'événements.
Bakuba fait partie de l'écosystème DarkWeb.fr, aux côtés de DarkWall,
DarkSh, DarkMake et Darkcontainer.

## État de cette publication

Cette première publication est une réinitialisation de l'état SVN r654,
sans reprise de l'ancien historique. Il s'agit d'un arbre historique et
expérimental, pas d'une version certifiée pour la production.

La préparation a retiré un identifiant secret SVN et deux occurrences
d'une adresse IP publique. Elle ne constitue pas un audit de sécurité.
Ne pas exposer directement ce serveur à Internet sans revue et tests.

Contrôles effectués avec PHP 8.5 : 36 fichiers sur 780 échouent à
`php -l`, exactement les mêmes que dans l'arbre d'origine. Sur les
39 scripts shell contrôlés avec `sh -n`, seul `testShStart` échoue,
également dans l'original. Aucun test fonctionnel complet ni test
d'exécution Windows n'a été réalisé.

## Point d'entrée

```sh
php start.php
```

Les exemples d'applications se trouvent dans `samples/`.

## Dépendances SVN externes (non incluses)

Le dépôt historique référençait deux externals SVN, extraits vers
leurs propres dépôts et non inclus dans cette archive :

- `darkphp` (à la racine)
- `log4dw` (dans `logger/`)

## Dépendances tierces incluses

- `extInc/reactphp/` — ReactPHP (MIT)
- `process/child/posix/shell/trash/makeself/` — makeself (GPL v2, voir `COPYING`)
- `process/child/posix/shell/obfsh` — obfuscateur shell de Dawid Michalczyk
- `process/child/posix/shell/trash/makeself/test/bashunit/` — bashunit
- `process/child/windows/JScript/lib-js/json2.js` — json2.js de Douglas Crockford (Public Domain)

Ces fichiers conservent leur licence d'origine.

## Licence

Code Bakuba : Apache License 2.0 — voir [LICENSE](LICENSE).

Copyright 2011-2026 Nicolas "DarkHack" Chapuis
