from pathlib import Path
text = Path(r'app\\Http\\Controllers\\SupervisorController.php').read_text()
start = text.index("            return [")
print(repr(text[start:start+80]))
