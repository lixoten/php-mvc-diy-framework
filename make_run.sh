#!/bin/bash
# make_run.sh - Run PHP console commands for entity generation

# Configuration
PHP_BIN="php"
CONSOLE_SCRIPT="bin/console.php"

# Entity from command-line argument, default to "user" if not provided
entity="${1:-user}"
Entity="$(tr '[:lower:]' '[:upper:]' <<< ${entity:0:1})${entity:1}"

#########################################################
# REUSABLE FUNCTION - Prompt and run console command
# Arguments:
#   $1 - Console command (e.g., "make:migration")
#   $2 - Command description (for prompts)
#   $3 - Step number
#########################################################
prompt_and_run() {
    local command="$1"
    local description="$2"
    local step="$3"
    local choice

    while true; do
        read -p "$step. Run $PHP_BIN $CONSOLE_SCRIPT $command $entity? (y/n/q): " choice
        case "$choice" in
            y|Y )
                echo "$Entity chose YES. Running $description..."
                $PHP_BIN $CONSOLE_SCRIPT $command $entity
                echo "$step. Successfully ran $description for $entity."
                echo ""
                break  # EXIT THE LOOP
                ;;
            n|N )
                echo "$step. $Entity chose NO. Skipping $description."
                echo ""
                break  # EXIT THE LOOP
                ;;
            q|Q )
                echo "$step. You chose QUIT. Exiting script."
                exit 0
                ;;
            * )
                echo "Invalid choice. Please enter y, n, or q."
                # Loop continues, prompts again
                ;;
        esac
    done

    read -p "Press Enter to continue..."
    echo ""
}

#########################################################
# Main Script
#########################################################

echo ""
echo "--- Running Console Commands for Entity: $entity ---"
echo ""

#########################################################
# 1. Generate Migration
#########################################################
prompt_and_run "make:migration" "Migration Generation" "1"

#########################################################
# 2. Generate Seeder
#########################################################
prompt_and_run "make:seeder" "Seeder Generation" "2"

#########################################################
# 3. Generate Entity
#########################################################
prompt_and_run "make:entity" "Entity Generation" "3"

#########################################################
# 4. Generate Field Config
#########################################################
prompt_and_run "make:field-config" "Field Config Generation" "4"

#########################################################
# 5. Move Files to Feature Directory
#########################################################
prompt_and_run "feature:move" "Feature Move" "5"

echo ""
echo "Script finished."