# -*- coding: utf-8 -*-
"""
curp_interna_tool.py
Lee "Deudores.txt" (una línea por nombre), genera CURPs internas,
lleva un contador por CURP y al final imprime solo las CURPs con conteo > 1.

Uso:
    python3 curp_interna_tool.py
"""

import os
import sys
import re
import unicodedata
from collections import defaultdict
from typing import Tuple

ALLOWED = set("ABCDEFGHIJKLMNOPQRSTUVWXYZ ")

def strip_diacritics(s: str) -> str:
    """Quita diacríticos (á->a, ñ->n, ü->u) usando NFKD."""
    nk = unicodedata.normalize("NFKD", s)
    return "".join(ch for ch in nk if not unicodedata.combining(ch))

def normalize_minimal(name: str) -> str:
    """
    Normalización mínima de FORMATO (sin suponer nada del texto):
    - Trim y colapsar espacios múltiples a 1
    - MAYÚSCULAS
    - Quitar diacríticos
    - Dejar solo A–Z y espacios
    """
    s = name.strip()
    s = re.sub(r"\s+", " ", s)
    s = s.upper()
    s = strip_diacritics(s)
    s = "".join(ch for ch in s if ch in ALLOWED)
    s = re.sub(r"\s+", " ", s).strip()
    return s

def curp_interna_from_name(name: str) -> Tuple[str, str, int, int]:
    """
    CURP interna = [primera+última de cada palabra concatenadas] + [LARGO_SIN_ESPACIOS] + [NUM_PALABRAS]
    Si una palabra tiene 1 letra, se duplica (p.ej. 'D' -> 'DD').
    Devuelve: (curp_interna, nombre_normalizado, largo_sin_espacios, num_palabras)
    """
    norm = normalize_minimal(name)
    if not norm:
        return ("", "", 0, 0)

    words = norm.split(" ")
    parts = []
    letters_count = 0
    for w in words:
        if len(w) == 1:
            parts.append(w + w)   # 1 letra -> duplicar para "primera+última"
            letters_count += 1
        else:
            parts.append(w[0] + w[-1])
            letters_count += len(w)

    base = "".join(parts)
    curp = f"{base}{letters_count}{len(words)}"
    return (curp, norm, letters_count, len(words))

def main():
    filename = "Deudores.txt"
    if not os.path.exists(filename):
        print(f"ERROR: No se encontró '{filename}' en la carpeta actual: {os.getcwd()}")
        print("Crea un archivo Deudores.txt con un nombre por línea y vuelve a ejecutar.")
        sys.exit(1)

    # Leer nombres (ignorando líneas vacías)
    with open(filename, "r", encoding="utf-8", errors="ignore") as f:
        raw_lines = [line.strip() for line in f if line.strip()]

    counts = defaultdict(int)         # CURP -> conteo
    examples = defaultdict(set)       # CURP -> set de nombres normalizados (para inspección)

    for raw in raw_lines:
        curp, norm, *_ = curp_interna_from_name(raw)
        if not curp:
            continue
        counts[curp] += 1
        examples[curp].add(norm)

    total = len(raw_lines)
    uniques = len(counts)
    dup_keys = [k for k, c in counts.items() if c > 1]

    print("==== RESUMEN ====")
    print(f"Total de líneas (nombres)   : {total}")
    print(f"CURPs internas únicas       : {uniques}")
    print(f"CURPs con coincidencias > 1 : {len(dup_keys)}")
    print()

    print("==== CURPs con conteo > 1 (posibles duplicados) ====")
    if not dup_keys:
        print("No se encontraron coincidencias mayores a 1.")
        return

    # Orden: primero por conteo desc, luego por clave
    dup_keys.sort(key=lambda k: (-counts[k], k))
    for k in dup_keys:
        nombres = ", ".join(sorted(examples[k]))
        print(f"{k} -> {counts[k]}  |  {nombres}")

if __name__ == "__main__":
    main()
