#!/bin/bash
# make_clean.sh - Simple cleanup for Generated folder

# Configuration
GENERATED_DIR="src/Generated"
MIGRATIONS_DIR="src/Database/Migrations"
SEEDERS_DIR="src/Database/Seeders"

# Entity from command-line argument, default to "user" if not provided
entity="${1:-user}"
Entity="$(tr '[:lower:]' '[:upper:]' <<< ${entity:0:1})${entity:1}"

# Dynamic paths based on entity
ENTITY_CONFIG_DIR="src/App/Features/${Entity}/Config"
ENTITY_DIR="src/App/Features/${Entity}"

#########################################################
# REUSABLE FUNCTION - Prompt and delete file(s)
# Arguments:
#   $1 - File pattern/path to delete
#   $2 - File type description (for prompts)
#   $3 - Step number
#########################################################
prompt_and_delete() {
    local file_pattern="$1"
    local file_type="$2"
    local step="$3"
    local choice

    # Check if any files match the pattern
    if ls $file_pattern 1> /dev/null 2>&1; then
        while true; do
            read -p "$file_type Delete $file_pattern? (y/n/q): " choice
            case "$choice" in
                y|Y )
                    echo "$Entity chose YES. $file_type - Deleting file(s)..."
                    rm -rf $file_pattern
                    echo "$step. Successfully deleted $file_type file(s): $file_pattern"
                    echo ""
                    break  # EXIT THE LOOP
                    ;;
                n|N )
                    echo "$step. $Entity chose NO. $file_type - Skipping deletion of $file_pattern."
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
    else
        echo "$step. No $file_type file(s) matching $file_pattern found. Skipping deletion."
        echo ""
    fi
    read -p "Press any key to continue..."
}

#########################################################
#########################################################
#########################################################
#########################################################
#########################################################
#########################################################
#########################################################

echo ""
echo "--- Cleaning Generated Files ---"
echo ""

#########################################################
# 1. Delete Generated Folder
#########################################################
prompt_and_delete "$GENERATED_DIR" "Generated Folder" "1"

#########################################################
# 2. Delete Migration File like *CreateUserTable.php
#########################################################
prompt_and_delete "${MIGRATIONS_DIR}/*Create${Entity}Table.php" "Migration" "2"

#########################################################
# 3. Delete Seeder File like UserSeeder.php
#########################################################
prompt_and_delete "${SEEDERS_DIR}/${Entity}Seeder.php" "Seeder" "3"

#########################################################
# 4. Delete field_entity File like field_user.php
#########################################################
prompt_and_delete "${ENTITY_CONFIG_DIR}/${entity}_fields.php" "Field Config" "4"

#########################################################
# 5. Delete Entity File like User.php
#########################################################
prompt_and_delete "${ENTITY_DIR}/${Entity}.php" "Entity Class" "5"

echo "Script finished."
























#########################################################
# 1. Delete Generated Folder
#
# Delete generated directory if it exists, with prompt
#########################################################
if [ -d "$GENERATED_DIR" ]; then
    read -p "Delete Folder $GENERATED_DIR? (y/n/q): " choice
    case "$choice" in
        y|Y )
            echo "You chose YES. Deleting folder..."
            rm -rf "$GENERATED_DIR"
            echo "Successfully deleted $GENERATED_DIR folder."
            ;;
        n|N )
            echo "You chose NO. Nothing deleted."
            ;;
        q|Q )
            echo "You chose QUIT. Exiting script."
            exit 0
            ;;
        * )
            echo "Invalid choice. Nothing deleted."
            ;;
    esac
else
    echo "Directory $GENERATED_DIR not present."
    echo ""
fi
echo ""





echo "Script finished."