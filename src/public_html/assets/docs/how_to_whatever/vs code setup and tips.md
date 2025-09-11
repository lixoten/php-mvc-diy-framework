# Tips and Extensions for vs Code

>- [Tips and Extensions for vs Code](#tips-and-extensions-for-vs-code)
- [Tips and Extensions for vs Code](#tips-and-extensions-for-vs-code)
  - [Extensions](#extensions)
    - [**GitHub Copilot** by Github](#github-copilot-by-github)
    - [**PHP Intelephense** by Ben Mewburn](#php-intelephense-by-ben-mewburn)
    - [**PHP Sniffer** by wongjn](#php-sniffer-by-wongjn)
    - [**Tabs Colors** by mondersky](#tabs-colors-by-mondersky)
    - [**Favorites** by kdcro101](#favorites-by-kdcro101)
    - [**multi-command** by ryuta46](#multi-command-by-ryuta46)
    - [**Snippets** are built into vs code. Extension can help u manage/create them. If you do not like doing them manually.](#snippets-are-built-into-vs-code-extension-can-help-u-managecreate-them-if-you-do-not-like-doing-them-manually)
    - [**PHP Debug** by Xdebug](#php-debug-by-xdebug)
    - [**GitLens — Git supercharged** by GitKraken](#gitlens--git-supercharged-by-gitkraken)
    - [**Breakpoints Manager** by Loukas Kotas](#breakpoints-manager-by-loukas-kotas)
    - [**Code Spell Checker** by Street Side Software](#code-spell-checker-by-street-side-software)
    - [**Highlight** by Fabio Spampinato\*\*](#highlight-by-fabio-spampinato)
    - [**Local History** by xyz](#local-history-by-xyz)
    - [**Tabstronaut - Tab Groups** by jhhtaylor](#tabstronaut---tab-groups-by-jhhtaylor)
    - [**xxxx** by xxxx](#xxxx-by-xxxx)
    - [**xxxx** by xxxx](#xxxx-by-xxxx-1)
    - [**ignore "g" it** by Andrea Vincenzo Abbondanza](#ignore-g-it-by-andrea-vincenzo-abbondanza)
    - [**Markdown Interactive Checkbox** by Bhnum](#markdown-interactive-checkbox-by-bhnum)
    - [**File Templates** by Bruno Paz](#file-templates-by-bruno-paz)
  - [TIPS- Shortcut keys I use](#tips--shortcut-keys-i-use)
    - [ctrl+/ - Add comments `//` and moves curser to next line](#ctrl---add-comments--and-moves-curser-to-next-line)
    - [ctrl+y - Delete Line](#ctrly---delete-line)
    - [ctrl+d -Duplicate Line, "down"](#ctrld--duplicate-line-down)
    - [ctrl+shift+/ - Reindent Selected lines](#ctrlshift---reindent-selected-lines)
    - [ctrl+left and ctrl+right - Navigate back or forward](#ctrlleft-and-ctrlright---navigate-back-or-forward)
    - [ctrl+alt+a and ctrl+alt+it -brings up two dictionary to add word too. words/ignore](#ctrlalta-and-ctrlaltit--brings-up-two-dictionary-to-add-word-too-wordsignore)
    - [xxxx](#xxxx)
    - [xxxx](#xxxx-1)
    - [xxxx](#xxxx-2)
    - [xxxx](#xxxx-3)


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
    "tabstronaut.newTabGroupPosition": "top",
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