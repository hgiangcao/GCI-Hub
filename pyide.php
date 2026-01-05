<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>GCI Online IDE</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt-stdlib.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { dark: { bg: '#1a1a1a', surface: '#282828' } } } } }
    </script>

    <style>
        /* Terminal Styling */
        #terminal-container {
            font-family: 'Courier New', monospace; font-size: 15px;
            color: #2cbb5d; background-color: #000;
        }
        .term-input {
            background: transparent; border: none; color: #fff;
            outline: none; width: 60%; caret-color: #2cbb5d;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1e1e1e; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }

        /* Resizer Handle */
        .gutter {
            width: 8px;
            background-color: #374151;
            cursor: col-resize;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .gutter:hover, .gutter.dragging {
            background-color: #3b82f6; /* Blue on hover */
        }
        /* Little grip lines inside gutter */
        .gutter::after {
            content: "";
            height: 20px;
            width: 2px;
            background: #9ca3af;
            border-radius: 1px;
        }
    </style>
</head>
<body class="bg-dark-bg h-screen flex flex-col overflow-hidden">


    <div class="h-14 border-b border-gray-700 flex justify-between items-center px-6 bg-dark-surface shrink-0">
        <div class="flex items-center gap-3">
            <span class="text-gray-200 font-bold text-lg"><i class="fa-brands fa-python text-blue-400"></i> GCI Online Python IDE</span>
        </div>

        <div class="flex gap-3">
            <button onclick="resetCode()" class="bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium py-1.5 px-4 rounded transition border border-gray-600">
                Reset
            </button>
            <button id="btn-run" onclick="runCode()" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold py-1.5 px-6 rounded shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-play"></i> Run Code
            </button>
        </div>
    </div>

    <div id="main-container" class="flex-grow flex w-full overflow-hidden bg-dark-surface">

        <div id="editor-panel" class="w-1/2 flex flex-col min-w-[200px]">
            <div class="bg-gray-800 text-gray-400 text-xs px-4 py-1 border-b border-gray-700 uppercase font-semibold">
                main.py
            </div>
            <div id="editor" class="flex-grow w-full text-base font-mono"></div>
        </div>

        <div id="resizer" class="gutter"></div>

        <div id="terminal-panel" class="flex-grow flex flex-col min-w-[200px] bg-black">
            <div class="bg-gray-900 text-gray-400 text-xs px-4 py-1 border-b border-gray-800 uppercase font-semibold flex justify-between">
                <span>Terminal</span>
                <span onclick="clearTerminal()" class="cursor-pointer hover:text-white"><i class="fa-solid fa-trash-can"></i> Clear</span>
            </div>
            <div id="terminal-container" class="flex-grow p-4 overflow-y-auto whitespace-pre-wrap leading-relaxed"></div>
        </div>

    </div>

<script>
    // --- 1. SETUP ACE EDITOR ---
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/monokai");
    editor.session.setMode("ace/mode/python");
    editor.setFontSize(16);
    editor.setShowPrintMargin(false);

    const defaultCode = `def main():
    print("Welcome to GCI Online IDE!")
    print("--------------------------")

    # Try changing this!
    limit = 5
    for i in range(limit):
        print(f"Counting: {i+1}")

if __name__ == "__main__":
    main()
`;
    editor.setValue(defaultCode, -1);

    function resetCode() {
        if(confirm("Reset code?")) editor.setValue(defaultCode, -1);
    }

    // --- 2. RESIZABLE COLUMNS LOGIC ---
    const resizer = document.getElementById('resizer');
    const leftPanel = document.getElementById('editor-panel');
    const container = document.getElementById('main-container');
    let isResizing = false;

    resizer.addEventListener('mousedown', (e) => {
        isResizing = true;
        resizer.classList.add('dragging');
        document.body.style.cursor = 'col-resize';
        leftPanel.style.userSelect = 'none'; // Prevent text selection
        editor.container.style.pointerEvents = 'none'; // Fix Ace Editor mouse capture
    });

    document.addEventListener('mousemove', (e) => {
        if (!isResizing) return;

        const containerRect = container.getBoundingClientRect();
        // Calculate new width relative to container
        let newWidth = ((e.clientX - containerRect.left) / containerRect.width) * 100;

        // Limits (10% to 90%)
        if (newWidth > 10 && newWidth < 90) {
            leftPanel.style.width = newWidth + '%';
            editor.resize(); // Tell Ace Editor to redraw
        }
    });

    document.addEventListener('mouseup', () => {
        if (isResizing) {
            isResizing = false;
            resizer.classList.remove('dragging');
            document.body.style.cursor = 'default';
            leftPanel.style.userSelect = 'auto';
            editor.container.style.pointerEvents = 'auto';
        }
    });

    // --- 3. TERMINAL & SKULPT LOGIC ---
    var terminal = document.getElementById("terminal-container");

    function clearTerminal() { terminal.innerHTML = ""; }

    function outf(text) {
        var span = document.createElement("span");
        if (text.startsWith("Error:") || text.includes("Traceback")) span.style.color = "#ef4444";
        span.innerText = text;
        terminal.appendChild(span);
        terminal.scrollTop = terminal.scrollHeight;
    }

    function builtinRead(x) {
        if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined) throw "File not found: '" + x + "'";
        return Sk.builtinFiles["files"][x];
    }

    function inputPlugin(prompt) {
        return new Promise(function(resolve, reject) {
            if (prompt) outf(prompt);
            var input = document.createElement("input");
            input.className = "term-input";
            input.setAttribute("type", "text");
            input.setAttribute("autocomplete", "off");
            input.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    var val = this.value;
                    this.remove();
                    outf(val + "\n");
                    resolve(val);
                }
            });
            terminal.appendChild(input);
            input.focus();
            terminal.scrollTop = terminal.scrollHeight;
        });
    }

    function runCode() {
        var prog = editor.getValue();
        clearTerminal();
        var btn = document.getElementById("btn-run");
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running...';
        btn.disabled = true;

        Sk.pre = "terminal-container";
        Sk.configure({ output: outf, read: builtinRead, inputfun: inputPlugin, inputfunTakesPrompt: true });

        Sk.misceval.asyncToPromise(function() {
            return Sk.importMainWithBody("<stdin>", false, prog, true);
        }).then(function(mod) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, function(err) {
            outf("\n" + err.toString());
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Auto focus input
    terminal.addEventListener('click', function() {
        var input = terminal.querySelector(".term-input");
        if(input) input.focus();
    });
</script>

</body>
</html>