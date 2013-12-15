#!/bin/bash
#
# Runs PHP CodeSniffer across the entire project, and runs the tests

# Echoes the path to the project root
#
# This function resolves symlinks and all kinds of possible indirection
# to find the actual source file, then navigates the project directory
# structure to find the root.
#
# The "return value" is echoed, so this function should be used like
# this:
#
#     RESULT=$(project_root)
#
# The body of this function was adapted from this StackOverflow post:
# http://stackoverflow.com/q/59895
function project_root {
    local file="${BASH_SOURCE[0]}"

    # Resolve $file until the file is no longer a symlink
    while [ -h "$file" ]; do
        local dir="$(cd -P "$(dirname "$file")" && pwd)"
        local file="$(readlink "$file")"

        # If $file was a relative symlink, we need to resolve it
        # relative to the path where the symlink file was located
        [[ $file != /* ]] && file="$dir/$file"
    done

    local this_dir="$(cd -P "$(dirname "$file")" && pwd)"
    echo "$(dirname "$this_dir")"
}


ROOT="$(project_root)"
PHPCS="$ROOT/vendor/bin/phpcs"
PHPUNIT="$ROOT/vendor/bin/phpunit"

# Choose which PHPUnit configuration file to use
if [[ $1 == '--no-color' ]]; then
    # --no-color specified as an argument to this script, disable color
    PHPUNIT_CONFIG="$ROOT/phpunit-no-color.xml.dist"
else
    # Enable color by default
    PHPUNIT_CONFIG="$ROOT/phpunit.xml.dist"
fi

# Abort this script if any command fails
set -e

# Run PHP CodeSniffer
"$PHPCS" --standard="$ROOT/phpcs.xml" "$ROOT"

# Run PHPUnit test suite
"$PHPUNIT" $PHPUNIT_FLAGS --configuration "$PHPUNIT_CONFIG"
