import PyPDF2
from pathlib import Path

path = Path(r"Documento_Maestro_Kualifin_ULTRA (1).pdf")
reader = PyPDF2.PdfReader(path.open("rb"))

with open("extract_pdf.txt", "w", encoding="utf-8") as out:
    out.write(f"Total pages: {len(reader.pages)}\n")
    for i, page in enumerate(reader.pages, 1):
        out.write(f"--- PAGE {i} ---\n")
        text = page.extract_text() or ""
        out.write(text + "\n")
