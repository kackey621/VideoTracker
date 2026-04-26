# Contributing

Thank you for your interest in contributing to HLS Player for Moodle!

Please read our [Code of Conduct](../code-of-conduct.md) before participating.

---

## Ways to Contribute

- **Bug reports** — open a GitHub Issue describing what went wrong
- **Feature requests** — open a GitHub Issue describing the use case
- **Pull requests** — fix a bug or implement a feature
- **Translations** — add a new language file

---

## Bug Reports

When reporting a bug, please include:

- Moodle version (e.g., 4.5.2)
- PHP version (e.g., 8.2.x)
- Browser and version
- Steps to reproduce the issue
- Expected vs. actual behaviour
- Any relevant error messages from Moodle's debug log

---

## Pull Requests

1. **Fork** the repository and create a branch from `main`:
    ```bash
    git checkout -b fix/your-fix-description
    ```

2. Follow [Moodle's coding style](https://moodledev.io/general/development/policies/codingstyle):
    - PHPDoc blocks on all public methods
    - Snake_case for function and variable names
    - 4-space indentation (no tabs)

3. **Rebuild the AMD JavaScript bundle** if you edit `amd/src/player.js`:
    ```bash
    cd hlsplayer
    npx grunt amd
    ```
    Commit both `amd/src/player.js` and `amd/build/player.min.js`.

4. **Run the CI checks locally** if possible:
    ```bash
    moodle-plugin-ci install --plugin . --db-type pgsql
    moodle-plugin-ci phpunit
    moodle-plugin-ci phplint
    moodle-plugin-ci codechecker
    ```

5. Submit your pull request against the `main` branch with a clear description of the changes.

---

## Translations

Language files live in `lang/{lang}/hlsplayer.php`. To add a new language:

1. Copy `lang/en/hlsplayer.php` to `lang/{your-lang}/hlsplayer.php`.
2. Translate the string values (keep the keys unchanged).
3. Follow [Moodle AMOS conventions](https://docs.moodle.org/dev/AMOS) for string formatting.
4. Submit a pull request.

---

## License

All contributions are licensed under [GPL v3 or later](https://github.com/kackey621/moodle-mod_hlsplayer/blob/main/hlsplayer/LICENSE), the same license as the project.
