#!/usr/bin/env bash

set -uo pipefail

fixturePath="tests/GoldenTest/fixture"

function exitWithFailure() {
    echo "$1"
    exit 1
}

# Reset the fixture to initial state
function cleanup {
    local ERROR=""

    ERROR=$(git reset --hard 2>&1) || echo "git reset --hard not succesful" "$ERROR"
    ERROR=$(git stash pop 2>&1) || echo "git stash pop not succesful" "$ERROR"
}

errorOutput=$(git stash 2>&1) || exitWithFailure "$errorOutput"
# From here, reset the fixture to initial state
trap cleanup EXIT
errorOutput=$(bin/update.php -vvv "$fixturePath" line-up 2>&1) || exitWithFailure "$errorOutput"
git diff --exit-code HEAD -- "$fixturePath"
