import os
import re
import subprocess
import time

DOCS_DIR = os.path.dirname(os.path.abspath(__file__))
CHROME_BIN = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

CSS_STYLES = """
@page {
    size: A4;
    margin: 20mm 15mm 20mm 15mm;
    @bottom-center {
        content: counter(page);
    }
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    line-height: 1.6;
    font-size: 10.5pt;
    background-color: #ffffff;
    margin: 0;
    padding: 0;
}

.cover-page {
    page-break-after: always;
    text-align: center;
    padding-top: 50px;
    height: 90vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cover-institution {
    font-size: 14pt;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.cover-subinstitution {
    font-size: 11pt;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    margin-bottom: 40px;
}

.cover-divider {
    width: 80px;
    height: 4px;
    background: #0284c7;
    margin: 0 auto 40px auto;
    border-radius: 2px;
}

.cover-title {
    font-size: 20pt;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 15px;
    text-transform: uppercase;
}

.cover-subtitle {
    font-size: 13pt;
    font-weight: 600;
    color: #0284c7;
    margin-bottom: 25px;
}

.cover-desc {
    font-size: 10pt;
    color: #64748b;
    max-width: 80%;
    margin: 0 auto 50px auto;
    line-height: 1.5;
}

.cover-meta-box {
    margin: 0 auto;
    width: 80%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    text-align: left;
    font-size: 10pt;
}

.cover-meta-box table {
    width: 100%;
    border-collapse: collapse;
}

.cover-meta-box td {
    padding: 6px 10px;
    border: none;
    background: transparent;
}

.cover-footer {
    margin-top: 50px;
    font-size: 11pt;
    font-weight: 700;
    color: #334155;
    letter-spacing: 1px;
}

h1 {
    font-size: 17pt;
    font-weight: 800;
    color: #0f172a;
    border-bottom: 2px solid #0284c7;
    padding-bottom: 6px;
    margin-top: 30px;
    margin-bottom: 15px;
    page-break-after: avoid;
}

h2 {
    font-size: 13.5pt;
    font-weight: 700;
    color: #0369a1;
    margin-top: 24px;
    margin-bottom: 10px;
    page-break-after: avoid;
}

h3 {
    font-size: 11.5pt;
    font-weight: 700;
    color: #1e293b;
    margin-top: 18px;
    margin-bottom: 8px;
    page-break-after: avoid;
}

p, li {
    font-size: 10pt;
    color: #334155;
    text-align: justify;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 9pt;
    page-break-inside: avoid;
}

th, td {
    border: 1px solid #cbd5e1;
    padding: 7px 10px;
    text-align: left;
}

th {
    background-color: #0f172a;
    color: #ffffff;
    font-weight: 600;
}

tr:nth-child(even) td {
    background-color: #f8fafc;
}

pre, code {
    font-family: "Cascadia Code", Consolas, "Courier New", monospace;
    font-size: 8.5pt;
}

code {
    background-color: #f1f5f9;
    color: #0f172a;
    padding: 2px 5px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}

pre {
    background-color: #0f172a;
    color: #f8fafc;
    padding: 12px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 12px 0;
    page-break-inside: avoid;
    white-space: pre-wrap;
    word-wrap: break-word;
}

pre code {
    background: transparent;
    border: none;
    color: inherit;
    padding: 0;
}

blockquote {
    border-left: 4px solid #0284c7;
    margin: 12px 0;
    padding: 8px 16px;
    background-color: #f0f9ff;
    color: #0369a1;
    font-style: italic;
    border-radius: 0 4px 4px 0;
    page-break-inside: avoid;
}

.page-break {
    page-break-after: always;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 8pt;
    font-weight: 600;
    border-radius: 4px;
    background: #e0f2fe;
    color: #0369a1;
}

.diagram-box {
    background-color: #0b1120;
    color: #38bdf8;
    border: 1px solid #1e293b;
    border-radius: 6px;
    padding: 12px;
    font-family: monospace;
    font-size: 8pt;
    margin: 12px 0;
    white-space: pre-wrap;
    page-break-inside: avoid;
}
"""

def md_to_html(md_text, is_sub=False):
    # Process basic markdown features
    lines = md_text.split("\n")
    html_lines = []
    in_code = False
    code_block = []
    in_table = False
    table_lines = []
    
    for line in lines:
        if line.startswith("```"):
            if in_code:
                in_code = False
                code_content = "\n".join(code_block)
                # Escaping html
                code_content = code_content.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
                html_lines.append(f"<pre><code>{code_content}</code></pre>")
                code_block = []
            else:
                in_code = True
                code_block = []
            continue
            
        if in_code:
            code_block.append(line)
            continue
            
        # Tables
        if "|" in line and not line.strip().startswith("```"):
            if not in_table:
                in_table = True
                table_lines = [line]
            else:
                table_lines.append(line)
            continue
        else:
            if in_table:
                in_table = False
                html_lines.append(render_table(table_lines))
                table_lines = []
                
        # Horizontal rules
        if re.match(r"^---+$", line.strip()):
            html_lines.append("<hr style='border: 0; height: 1px; background: #e2e8f0; margin: 20px 0;'/>")
            continue
            
        # Headings
        if line.startswith("# "):
            title = line[2:].strip()
            html_lines.append(f"<h1>{title}</h1>")
            continue
        elif line.startswith("## "):
            title = line[3:].strip()
            html_lines.append(f"<h2>{title}</h2>")
            continue
        elif line.startswith("### "):
            title = line[4:].strip()
            html_lines.append(f"<h3>{title}</h3>")
            continue
        elif line.startswith("#### "):
            title = line[5:].strip()
            html_lines.append(f"<h4>{title}</h4>")
            continue
            
        # Blockquotes
        if line.startswith("> "):
            content = parse_inlines(line[2:].strip())
            html_lines.append(f"<blockquote>{content}</blockquote>")
            continue
            
        # List items
        if re.match(r"^\s*[\*\-]\s+(.*)", line):
            m = re.match(r"^\s*[\*\-]\s+(.*)", line)
            content = parse_inlines(m.group(1))
            html_lines.append(f"<li style='margin-left: 20px;'>{content}</li>")
            continue
            
        if re.match(r"^\s*\d+\.\s+(.*)", line):
            m = re.match(r"^\s*\d+\.\s+(.*)", line)
            content = parse_inlines(m.group(1))
            html_lines.append(f"<li style='margin-left: 20px;'>{content}</li>")
            continue
            
        # Paragraph
        stripped = line.strip()
        if stripped:
            html_lines.append(f"<p>{parse_inlines(stripped)}</p>")
            
    if in_table:
        html_lines.append(render_table(table_lines))
        
    return "\n".join(html_lines)

def parse_inlines(text):
    text = text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    # Bold
    text = re.sub(r"\*\*(.*?)\*\*", r"<strong>\1</strong>", text)
    # Italic
    text = re.sub(r"\*(.*?)\*", r"<em>\1</em>", text)
    # Inline code
    text = re.sub(r"`(.*?)`", r"<code>\1</code>", text)
    # Links
    text = re.sub(r"\[(.*?)\]\((.*?)\)", r"<a href='\2' style='color:#0284c7;'>\1</a>", text)
    return text

def render_table(table_lines):
    if len(table_lines) < 2:
        return ""
    header_line = table_lines[0]
    headers = [h.strip() for h in header_line.split("|")[1:-1]]
    
    rows = []
    for line in table_lines[2:]:
        cols = [c.strip() for c in line.split("|")[1:-1]]
        rows.append(cols)
        
    html = ["<table><thead><tr>"]
    for h in headers:
        html.append(f"<th>{parse_inlines(h)}</th>")
    html.append("</tr></thead><tbody>")
    for row in rows:
        html.append("<tr>")
        for i, col in enumerate(row):
            html.append(f"<td>{parse_inlines(col)}</td>")
        html.append("</tr>")
    html.append("</tbody></table>")
    return "".join(html)

def build_pdf(html_content, output_pdf):
    temp_html = output_pdf.replace(".pdf", ".temp.html")
    full_html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dokumen Resmi Proyek Akhir RPL</title>
<style>{CSS_STYLES}</style>
</head>
<body>
{html_content}
</body>
</html>"""
    with open(temp_html, "w", encoding="utf-8") as f:
        f.write(full_html)
        
    url = "file:///" + os.path.abspath(temp_html).replace("\\", "/")
    cmd = [
        CHROME_BIN,
        "--headless=new",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={output_pdf}",
        url
    ]
    subprocess.run(cmd, check=True)
    time.sleep(1)
    if os.path.exists(temp_html):
        os.remove(temp_html)
    print(f"Generated PDF: {output_pdf} ({os.path.getsize(output_pdf)} bytes)")

def make_official_cover():
    return """
<div class="cover-page">
    <div>
        <div class="cover-institution">STMIK IKMI CIREBON</div>
        <div class="cover-subinstitution">Program Studi S1 Teknik Informatika</div>
        <div class="cover-divider"></div>
        
        <div class="cover-title">BERKAS LAPORAN LENGKAP PROYEK AKHIR MANDIRI & DOKUMENTASI REKAYASA PERANGKAT LUNAK</div>
        <div class="cover-subtitle">AETHEREAL JOKI: GAMING MARKETPLACE & JASA JOKI TERPERCAYA BERBASIS ARTIFICIAL INTELLIGENCE</div>
        <div class="cover-desc">
            Diajukan sebagai Berkas Dokumen Tunggal Pemenuhan Tugas Akhir Mandiri dan Rubrik Penilaian Outcome-Based Education (OBE) Semester 3 Tahun Akademik 2026/2027.
        </div>
    </div>
    
    <div class="cover-meta-box">
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Mata Kuliah / Kode</td>
                <td style="width: 5%;">:</td>
                <td>Rekayasa Perangkat Lunak (TIKB2503021) / 4 SKS (2 Teori + 2 Praktik)</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Dosen Pengampu</td>
                <td>:</td>
                <td><strong>Yudhistira Arie Wijaya, M.Kom., CDS.</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tema Topik Kelompok</td>
                <td>:</td>
                <td>Sistem Informasi Berbasis Web untuk Pemberdayaan UMKM Kreatif Digital & Ekosistem Gaming Cerdas Berbasis AI</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nama Mahasiswa</td>
                <td>:</td>
                <td><strong>Farrel Surya Pratama</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">NIM</td>
                <td>:</td>
                <td>41230001</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Sifat Pengerjaan</td>
                <td>:</td>
                <td>Mandiri / Perorangan (100% Individual Ownership & Full-Stack System Build)</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Status Pengujian & CI/CD</td>
                <td>:</td>
                <td><strong>117 Automated Unit/Feature Tests Passed (100% Green Pipeline)</strong></td>
            </tr>
        </table>
    </div>
    
    <div class="cover-footer">
        CIREBON — JAWA BARAT<br>
        2026
    </div>
</div>
"""

def main():
    files = [
        ("01_PRD_SRS_IEEE830.md", "PRD_SRS_Aethereal_Joki.pdf"),
        ("02_SDD_UML_Architecture.md", "SDD_UML_Aethereal_Joki.pdf"),
        ("03_Test_Report_and_DevOps_CICD.md", "Laporan_Pengujian_DevOps_Aethereal_Joki.pdf"),
        ("04_Naskah_Video_Presentasi_1_2_Final.md", "Naskah_Video_Presentasi_Aethereal_Joki.pdf"),
    ]
    
    # 1. Generate individual PDFs
    combined_body = [make_official_cover()]
    
    for md_file, pdf_name in files:
        md_path = os.path.join(DOCS_DIR, md_file)
        pdf_path = os.path.join(DOCS_DIR, pdf_name)
        with open(md_path, "r", encoding="utf-8") as f:
            content = f.read()
        
        html_part = md_to_html(content)
        build_pdf(html_part, pdf_path)
        
        combined_body.append("<div class='page-break'></div>")
        combined_body.append(html_part)
        
    # 2. Generate Master Combined Document (Bab V Poin 2 Berkas Dokumen Lengkap PDF Tunggal)
    master_pdf = os.path.join(DOCS_DIR, "LAPORAN_LENGKAP_PROYEK_AKHIR_RPL.pdf")
    build_pdf("\n".join(combined_body), master_pdf)
    print("\nALL PDF DELIVERABLES GENERATED SUCCESSFULLY!")

if __name__ == "__main__":
    main()
