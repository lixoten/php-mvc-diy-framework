# Tips and Extensions for vs Code

>- [Tips and Extensions for vs Code](#tips-and-extensions-for-vs-code)
1. [Tips and Extensions for vs Code](#tips-and-extensions-for-vs-code)
    1. [Extensions](#extensions)
        1. [**GitHub Copilot** by Github](#github-copilot-by-github)
        2. [**PHP Intelephense** by Ben Mewburn](#php-intelephense-by-ben-mewburn)
        3. [**PHP Sniffer** by wongjn](#php-sniffer-by-wongjn)
        4. [**Tabs Colors** by mondersky](#tabs-colors-by-mondersky)
        5. [**Favorites** by kdcro101](#favorites-by-kdcro101)
        6. [**multi-command** by ryuta46](#multi-command-by-ryuta46)
        7. [**Snippets** are built into vs code. Extension can help u manage/create them. If you do not like doing them manually.](#snippets-are-built-into-vs-code-extension-can-help-u-managecreate-them-if-you-do-not-like-doing-them-manually)
        8. [**PHP Debug** by Xdebug](#php-debug-by-xdebug)
        9. [**GitLens — Git supercharged** by GitKraken](#gitlens--git-supercharged-by-gitkraken)
        10. [**Breakpoints Manager** by Loukas Kotas](#breakpoints-manager-by-loukas-kotas)
        11. [**Code Spell Checker** by Street Side Software](#code-spell-checker-by-street-side-software)
        12. [**Highlight** by Fabio Spampinato\*\*](#highlight-by-fabio-spampinato)
        13. [**Local History** by xyz](#local-history-by-xyz)
        14. [**Tabstronaut - Tab Groups** by jhhtaylor](#tabstronaut---tab-groups-by-jhhtaylor)
        15. [**TabOut** by Albert Romkes](#tabout-by-albert-romkes)
        16. [**Activitus Bar** by Gruntfuggly](#activitus-bar-by-gruntfuggly)
        17. [**Statusbar Debugger** by Fabio Spampinato](#statusbar-debugger-by-fabio-spampinato)
        18. [**Fast Compare** by David Kolář](#fast-compare-by-david-kolář)
        19. [**xxxx** by xxxx](#xxxx-by-xxxx)
        20. [**xxxx** by xxxx](#xxxx-by-xxxx-1)
        21. [**ignore "g" it** by Andrea Vincenzo Abbondanza](#ignore-g-it-by-andrea-vincenzo-abbondanza)
        22. [**Markdown Interactive Checkbox** by Bhnum](#markdown-interactive-checkbox-by-bhnum)
        23. [**File Templates** by Bruno Paz](#file-templates-by-bruno-paz)
    2. [TIPS- Shortcut keys I use](#tips--shortcut-keys-i-use)
        1. [ctrl+/ - Add comments `//` and moves curser to next line](#ctrl---add-comments--and-moves-curser-to-next-line)
        2. [ctrl+y - Delete Line](#ctrly---delete-line)
        3. [ctrl+d -Duplicate Line, "down"](#ctrld--duplicate-line-down)
        4. [ctrl+shift+/ - Reindent Selected lines](#ctrlshift---reindent-selected-lines)
        5. [ctrl+left and ctrl+right - Navigate back or forward](#ctrlleft-and-ctrlright---navigate-back-or-forward)
        6. [ctrl+alt+a and ctrl+alt+it -brings up two dictionary to add word too. words/ignore](#ctrlalta-and-ctrlaltit--brings-up-two-dictionary-to-add-word-too-wordsignore)
        7. [xxxx](#xxxx)
        8. [xxxx](#xxxx-1)
        9. [xxxx](#xxxx-2)
        10. [xxxx](#xxxx-3)


## Extensions

### **GitHub Copilot** by Github

### **PHP Intelephense** by Ben Mewburn

### **PHP Sniffer** by wongjn
* I use it for standards PSR12
* Alternative that am not using
    - **PHP Sniffer & Beautifier** by Samuel Hilson

### **Tabs Colors** by mondersky
* Control tab colors, specially for active tab, to make it more visible

### **Favorites** by kdcro101
* It allows me to add/bookmark files that are external to my project
* note: setting, .favorites.json in project root
```json
    "favorites.sortDirection": "ASC",
    "favorites.groupsFirst": false,
```
### **multi-command** by ryuta46
* Like a macro, i use it for php // comments. it sets/unset's and moves to next line

* **TODO Highlight** by Wayou Liu
### **Snippets** are built into vs code. Extension can help u manage/create them. If you do not like doing them manually.
* File Location : C:\Users\rudyt\AppData\Roaming\Code\User\snippets

### **PHP Debug** by Xdebug
* Supports VS Code

### **GitLens — Git supercharged** by GitKraken
* not quite sure

### **Breakpoints Manager** by Loukas Kotas
* Manage breakpoint collections - i can place them in groups and save them


### **Code Spell Checker** by Street Side Software
* Add work to a Dictionary or to ignore
* tracks them using ext create files
```json
    "cSpell.customDictionaries": {
        "dictionary-words": {
            "name": "dictionary-words",
            "path": "${workspaceFolder}/.cspell/dictionary-words.txt",
            "addWords": true,
            "scope": "user"
        },
        "dictionary-ignore": {
            "name": "dictionary-ignore",
            "path": "${workspaceFolder}/.cspell/dictionary-ignore.txt",
            "addWords": true,
            "scope": "user"
        },
    },
    "cSpell.showCommandsInEditorContextMenu": true,
    "cSpell.mergeCSpellSettingsFields": {
        "userWords": true,
        "ignoreWords": true
    },
```

### **Highlight** by Fabio Spampinato**
* A TODO Highlight - Whole lines, words, start to end of line. uses regexes
```json
    // just a sample
    "highlight.regexes": {
        "((?:<!-- *)?(?:#|// @|//|./\\*+|<!--|--|\\* @|{!|{{!--|{{!) *TODO(?:\\s*\\([^)]+\\))?:?)((?!\\w)(?: *-->| *\\*/| *!}| *--}}| *}}|(?= *(?:[^:]//|/\\*+|<!--|@|--|{!|{{!--|{{!))|(?: +[^\\n@]*?)(?= *(?:[^:]//|/\\*+|<!--|@|--(?!>)|{!|{{!--|{{!))|(?: +[^@\\n]+)?))": {
            "filterFileRegex": ".*(?<!CHANGELOG.md)$",
            "decorations": [
                {
                    "overviewRulerColor": "#ffcc00",
                    "backgroundColor": "#ffcc00",
                    "color": "#1f1f1f",
                    "fontWeight": "bold"
                },
                {
                    "backgroundColor": "#ffcc00",
                    "color": "#1f1f1f"
                }
            ]
        },
```

### **Local History** by xyz
* Save files into local history - i placed them to point to an external drive outside my project workspace
```json
    "local-history.daysLimit": 0,
    "local-history.saveDelay": 30,
    "local-history.path": "D:\\xampp\\htdocs\\my_projects\\LocalHistory",
```

### **Tabstronaut - Tab Groups** by jhhtaylor
- Group tabs vertically into groups - Really handy to load a set of files at once
```json
    ///////////////////////////////////////////////////////
    // **Tabstronaut - Tab Groups** by jhhtaylor
    ///////////////////////////////////////////////////////
    "tabstronaut.newTabGroupPosition": "top",
    ///////////////////////////////////////////////////////
```


### **TabOut** by Albert Romkes
- Tab out of quotes, brackets, etc for Visual Studio Code.
- Really handy. Example: 'text' if inside the '' after the last character when u tab, it takes you out.
```json
  xxx
```


### **Activitus Bar** by Gruntfuggly
- Save some real estate by recreating the activity bar buttons on the status bar
- blue bottom bar center, black icons

```json
    ///////////////////////////////////////////////////////
    // **Activitus Bar** by Gruntfuggly

    // "activitusbar.toggleSidebar": true,
    "activitusbar.views": [
        { "codicon": "kebab-vertical" },
        { "name": "explorer", "codicon": "explorer-view-icon" },
        { "name": "debug",    "codicon": "run-view-icon" },
        { "name": "settings", "codicon": "gear" },
        { "name": "command.workbench.action.showCommands", "codicon": "symbol-event" },
        { "name": "command.workbench.action.reloadWindow", "codicon": "refresh" },
        { "codicon": "kebab-vertical" },
    ],
    ///////////////////////////////////////////////////////
```

🐛 - Bug (the most common one)
🪲 - Beetle (similar to bug)
🐞 - Lady Beetle/Ladybug
🕷️ - Spider
🦗 - Cricket
⚠️ - Warning
❌ - Error/Cross Mark
✅ - Success/Check Mark
🔴 - Red Circle (for critical issues)
🟡 - Yellow Circle (for warnings)
🟢 - Green Circle (for passing/success)
💥 - Collision/Explosion (for breaking changes)
🔥 - Fire (for critical bugs or "this is on fire")
🚨 - Police Light (for alerts)
⛔ - No Entry (for blockers)
🐛 Bug: This will expose underlying data issues
🐛 Fix null pointer exception in UserRepository
🐛 Warning: This code has a potential race condition



### **Statusbar Debugger** by Fabio Spampinato
- Adds a debugger to the statusbar, less intrusive than the default floating one
- easy to access debug button 🪲 in bottom bar. When active shows other debug buttons
```json
    ////////////////////////////////////////////////
    //Statusbar Debugger by Fabio Spampinato
    "statusbarDebugger.alignment": "left", // Should the item be placed to the left or right?
    "statusbarDebugger.priority": -10, // The priority of this item. Higher value means the item should be shown more to the left
    // "debug.toolBarLocation": "hidden",
    // "debug.showInStatusBar": "never",
    "statusbarDebugger.actions": [
        "bug",
        "pause",
        "continue",
        "step_over",
        "step_into",
        "step_out",
        "restart",
        "stop",
    ],
    "statusbarDebugger.actionsCommands": [
        "statusbarDebugger.toggle",
        "workbench.action.debug.pause",
        "workbench.action.debug.continue",
        "workbench.action.debug.stepOver",
        "workbench.action.debug.stepInto",
        "workbench.action.debug.stepOut",
        "statusbarDebugger.restart",
        "workbench.action.debug.stop",
    ],
    "statusbarDebugger.actionsIcons": [
        "$(bug)",
        "$(debug-pause)",
        "$(debug-continue)",
        "$(debug-step-over)",
        "$(debug-step-into)",
        "$(debug-step-out)",
        "$(debug-step-back)",
        "$(debug-stop)",
    ],
    "statusbarDebugger.actionsTooltips": [
        "Toggle Debugging",
        "Pause",
        "Continue",
        "Step Over",
        "Step Into",
        "Step Out",
        "Restart",
        "Stop",
    ],
    ////////////////////////////////////////////////
```

### **Fast Compare** by David Kolář
- Enables fast way of comparing two files via Context Menu Action on Text Editor Title.
- bit weird but works great
```json
  // No Settings...
```

### **xxxx** by xxxx
- xxx
```json
  xxx
```

### **xxxx** by xxxx
- xxx
```json
  xxx
```

### **ignore "g" it** by Andrea Vincenzo Abbondanza
- it allows you to add file to .gitignore via vscode UI. Right Click on file for menu


### **Markdown Interactive Checkbox** by Bhnum
- Allows u to have checkboxes in Markdown
- [x] checked item
- [ ] unchecked item


### **File Templates** by Bruno Paz
-Allows us to create new template files or create a file from existing templates.
- I used is to for my `favories.js`
- use:
    - >Files: New....
- See my Doc: src\public_html\assets\docs\HowToWhatever.md

## TIPS- Shortcut keys I use
- List Of Shortcut Keys I set up
  - > Preferences: Open Keyboard Shortcuts (JSON)
  - C:\Users\rudyt\AppData\Roaming\Code\User\keybindings.json
  -
### ctrl+/ - Add comments `//` and moves curser to next line
```json
{
  "key": "ctrl+/",
  "command": "multiCommand.commentAndMoveDown",
  "when": "editorTextFocus && editorLangId == 'php'"
}
```
### ctrl+y - Delete Line
```json
{
  "key": "ctrl+y",
  "command": "editor.action.deleteLines",
  "when": "textInputFocus && !editorReadonly"
}
```
### ctrl+d -Duplicate Line, "down"
```json
  {
    "key": "ctrl+d",
    "command": "editor.action.copyLinesDownAction",
    "when": "editorTextFocus && !editorReadonly"
  }
```

### ctrl+shift+/ - Reindent Selected lines
    - It just lines up shit.
```json
{
  "key": "ctrl+shift+/",
  "command": "editor.action.reindentselectedlines",
  "when": "editorTextFocus && !editorReadonly"
}
```

### ctrl+left and ctrl+right - Navigate back or forward
```json
{
  "key": "ctrl+left",
  "command": "workbench.action.navigateBack",
  "when": "canNavigateBack"
}
{
  "key": "ctrl+right",
  "command": "workbench.action.navigateForward",
  "when": "canNavigateForward"
}
```

### ctrl+alt+a and ctrl+alt+it -brings up two dictionary to add word too. words/ignore
```json
    {
        "key": "ctrl+alt+a",
        "command": "cSpell.addWordToUserDictionary",
        "when": "editorTextFocus",
        // "args": "dictionary-words" // bug, args not yet available
    },
    {
        "key": "ctrl+alt+i",
        "command": "cSpell.addWordToUserDictionary",
        "when": "editorTextFocus",
        // "args": "dictionary-ignore" // bug, args not yet available
    },
```

### xxxx
```json
  xxx
```

### xxxx
```json
  xxx
```

### xxxx
```json
  xxx
```

### xxxx
```json
  xxx
```