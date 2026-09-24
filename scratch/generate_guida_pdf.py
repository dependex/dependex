# -*- coding: utf-8 -*-
"""
Generatore PDF ufficiale per la Guida Gratuita di DEPENDEX.SOCIAL:
"I Primi 7 Giorni: Guida di Orientamento per la Famiglia"
Genera il file PDF A4 ad alta definizione in assets/docs/Guida_Primi_7_Giorni_Famiglia_DEPENDEX.pdf
"""

import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

def generate_guida_pdf(output_path: str):
    # Prova a registrare font TrueType se presenti
    font_main = 'Helvetica'
    font_bold = 'Helvetica-Bold'
    font_oblique = 'Helvetica-Oblique'

    arial_path = 'C:/Windows/Fonts/arial.ttf'
    arial_bd_path = 'C:/Windows/Fonts/arialbd.ttf'
    arial_it_path = 'C:/Windows/Fonts/ariali.ttf'

    if os.path.exists(arial_path) and os.path.exists(arial_bd_path):
        try:
            pdfmetrics.registerFont(TTFont('DxArial', arial_path))
            pdfmetrics.registerFont(TTFont('DxArial-Bold', arial_bd_path))
            if os.path.exists(arial_it_path):
                pdfmetrics.registerFont(TTFont('DxArial-Italic', arial_it_path))
            font_main = 'DxArial'
            font_bold = 'DxArial-Bold'
            font_oblique = 'DxArial-Italic' if os.path.exists(arial_it_path) else 'DxArial'
        except Exception:
            pass

    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=36,
        rightMargin=36,
        topMargin=32,
        bottomMargin=32
    )

    styles = getSampleStyleSheet()

    header_tag_style = ParagraphStyle(
        'HeaderTag',
        parent=styles['Normal'],
        fontName=font_bold,
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#854d0e'),
        alignment=TA_CENTER
    )

    title_style = ParagraphStyle(
        'MainTitle',
        parent=styles['Title'],
        fontName=font_bold,
        fontSize=19,
        leading=23,
        textColor=colors.HexColor('#0f172a'),
        alignment=TA_CENTER
    )

    subtitle_style = ParagraphStyle(
        'Subtitle',
        parent=styles['Normal'],
        fontName=font_main,
        fontSize=9.5,
        leading=13.5,
        textColor=colors.HexColor('#475569'),
        alignment=TA_CENTER
    )

    quote_style = ParagraphStyle(
        'Quote',
        parent=styles['Normal'],
        fontName=font_oblique,
        fontSize=9,
        leading=13.5,
        textColor=colors.HexColor('#1e293b')
    )

    quote_author_style = ParagraphStyle(
        'QuoteAuthor',
        parent=styles['Normal'],
        fontName=font_bold,
        fontSize=8,
        leading=11,
        textColor=colors.HexColor('#0f172a')
    )

    day_title_style = ParagraphStyle(
        'DayTitle',
        parent=styles['Heading2'],
        fontName=font_bold,
        fontSize=10.5,
        leading=13.5,
        textColor=colors.HexColor('#78350f')
    )

    day_text_style = ParagraphStyle(
        'DayText',
        parent=styles['Normal'],
        fontName=font_main,
        fontSize=8.5,
        leading=12.5,
        textColor=colors.HexColor('#334155'),
        alignment=TA_JUSTIFY
    )

    footer_style = ParagraphStyle(
        'FooterNote',
        parent=styles['Normal'],
        fontName=font_main,
        fontSize=7.5,
        leading=10.5,
        textColor=colors.HexColor('#64748b'),
        alignment=TA_CENTER
    )

    story = []

    # Tag Badge
    tag_data = [[
        Paragraph("<b>GUIDA PRATICA DI PRIMO SOCCORSO RELAZIONALE - METODO HUDOLIN</b>", header_tag_style)
    ]]
    t_tag = Table(tag_data, colWidths=[523])
    t_tag.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#fef08a')),
        ('ROUNDEDCORNERS', [4, 4, 4, 4]),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('TOPPADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(t_tag)
    story.append(Spacer(1, 6))

    # Main Title & Subtitle
    story.append(Paragraph("I Primi 7 Giorni: Orientamento per la Famiglia", title_style))
    story.append(Spacer(1, 3))
    story.append(Paragraph(
        "Cosa fare stasera, cosa non dire mai e come riaprire un dialogo autentico e liberarsi dalla solitudine secondo il Metodo Ecologico-Sociale di Vladimir Hudolin.",
        subtitle_style
    ))
    story.append(Spacer(1, 8))

    # Hudolin Quote Box
    quote_data = [[
        Paragraph(
            "\"L'alcolismo non e' una tara genetica ne' una malattia misteriosa dell'individuo, ma un comportamento legato allo stile di vita e una sofferenza della famiglia e della comunita'. La liberazione inizia quando smettiamo di cercare colpevoli e ci sediamo insieme in un cerchio di pari senza giudizio.\"",
            quote_style
        )
    ], [
        Paragraph("- Prof. Vladimir Hudolin, Psichiatra OMS e Fondatore dei Club Alcologici Territoriali", quote_author_style)
    ]]
    t_quote = Table(quote_data, colWidths=[523])
    t_quote.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f8fafc')),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
        ('LINELEFT', (0,0), (0,-1), 3.5, colors.HexColor('#d4af37')),
        ('PADDING', (0,0), (-1,-1), 7),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(t_quote)
    story.append(Spacer(1, 10))

    # 7 Days Content
    days = [
        ("GIORNO 1 - Sospendere l'Inquisizione e il Ricatto Emotivo",
         "La prima reazione spontanea e' cercare le bottiglie nascoste, contare i bicchieri o minacciare ultimatum. Questi comportamenti aumentano il segreto, la vergogna e la ribellione. Stasera dichiara semplicemente con calma: <i>\"Vedo la tua sofferenza e sento la mia. Ti voglio bene, ma non posso piu' far finta di non vedere. Possiamo chiedere aiuto insieme\".</i>"),
        ("GIORNO 2 - Separare la Persona dal Comportamento",
         "La persona che ami non coincide con il problema. La dipendenza e' una nebbia biochimica e relazionale che ne offusca la lucidita'. Parlare con una persona quando e' sotto effetto dell'alcol e' inutile e distruttivo: rimanda qualsiasi confronto al mattino o al momento di massima calma, parlando solo di cio' che provi tu senza accusare."),
        ("GIORNO 3 - Uscire dall'Isolamento e dalla Vergogna Domestica",
         "La dipendenza prospera nel silenzio e nella vergogna. Spesso la famiglia nasconde la situazione ad amici e parenti per paura del giudizio sociale. Ricorda che in Italia oltre 400.000 famiglie affrontano o hanno affrontato lo stesso percorso. Non sei solo/a, non hai colpe da espiare e chiedere aiuto e' il primo atto di vera liberta'."),
        ("GIORNO 4 - La Famiglia Puo' Andare al Club Anche da Sola",
         "Se il tuo familiare nega o rifiuta di farsi aiutare, <b>tu puoi comunque recarti al Club</b>. Nell'Approccio Ecologico-Sociale la famiglia non e' spettatrice passiva, e' protagonista attiva del cambiamento. Quando la famiglia frequenta il Club e muta il clima emotivo di casa, la persona con il problema spesso sceglie di unirsi spontaneamente."),
        ("GIORNO 5 - Trovare la Sedia Libera piu' Vicina a Casa",
         "Nei Club Territoriali non ci sono registri esposti, cartelle cliniche ne' costi d'ingresso. L'accoglienza e' immediata, libera e protetta dal segreto di gruppo. Consulta la mappa su <b>dependex.social</b> o chiama il Numero Verde per individuare il giorno e l'orario di riunione piu' comodo nel tuo territorio."),
        ("GIORNO 6 - Il Primo Incontro nel Cerchio Multifamiliare",
         "Al primo incontro nessuno ti costringera' a parlare. Puoi semplicemente sederti, bere un bicchiere d'acqua e ascoltare le storie di chi ci e' gia' passato e vive libero da 5, 10 o 25 anni. Vedere con i propri occhi che la serenita' familiare e' raggiungibile e' l'esperienza piu' rassicurante e curativa che esista."),
        ("GIORNO 7 - Un Giorno alla Volta: La Nuova Routine e la Serenita'",
         "Non si decide la sobrieta' 'per sempre': si sceglie con dignita' la giornata di oggi. Con il supporto settimanale del Club e del Servitore-Insegnante, la famiglia ricostruisce la fiducia e il rispetto reciproco passo dopo passo, una settimana alla volta.")
    ]

    for title, text in days:
        day_box_data = [
            [Paragraph(f"<b>{title}</b>", day_title_style)],
            [Paragraph(text, day_text_style)]
        ]
        t_day = Table(day_box_data, colWidths=[523])
        t_day.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#fdfbf7')),
            ('LINELEFT', (0,0), (0,-1), 3, colors.HexColor('#e0a96d')),
            ('TOPPADDING', (0,0), (-1,-1), 4),
            ('BOTTOMPADDING', (0,0), (-1,-1), 4),
            ('LEFTPADDING', (0,0), (-1,-1), 7),
            ('RIGHTPADDING', (0,0), (-1,-1), 7),
        ]))
        story.append(t_day)
        story.append(Spacer(1, 5))

    story.append(Spacer(1, 4))

    # Box Contatti e Rete
    contacts_data = [
        [
            Paragraph("<b>SEGRETERIA ACCOGLIENZA & RETE TERRITORIALE DEI CLUB (1.770 PRESIDI IN ITALIA)</b>", ParagraphStyle(
                'BoxTitle', parent=styles['Normal'], fontName=font_bold, fontSize=8.5, textColor=colors.HexColor('#d4af37'), alignment=TA_CENTER
            ))
        ],
        [
            Paragraph(
                "Numero Verde Nazionale AICAT: <b>800 974250</b> | Email Riservata: <b>info@dependex.support</b><br/>"
                "Mappa Mondiale dei Presidi: <b>https://dependex.social</b> | <b>https://oltre.social</b><br/>"
                "<i>Partecipazione sempre libera e gratuita. Nessuna prescrizione medica o trafila burocratica.</i>",
                ParagraphStyle(
                    'BoxBody', parent=styles['Normal'], fontName=font_main, fontSize=8, leading=11, textColor=colors.HexColor('#f8fafc'), alignment=TA_CENTER
                )
            )
        ]
    ]
    t_contacts = Table(contacts_data, colWidths=[523])
    t_contacts.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#0b101d')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#00d4ff')),
        ('PADDING', (0,0), (-1,-1), 7),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
    ]))
    story.append(t_contacts)

    story.append(Spacer(1, 6))
    story.append(Paragraph(
        "Documento divulgativo a cura della rete <b>DEPENDEX & OLTRE</b> - Metodo Ecologico-Sociale di Vladimir Hudolin - Legge quadro nazionale 30 marzo 2001, n. 125.",
        footer_style
    ))

    doc.build(story)
    print(f"[OK] PDF generato con successo: {output_path} (Dimensione: {os.path.getsize(output_path)} bytes)")

if __name__ == '__main__':
    out_dir = os.path.join(os.path.dirname(__file__), '..', 'assets', 'docs')
    os.makedirs(out_dir, exist_ok=True)
    out_file = os.path.join(out_dir, 'Guida_Primi_7_Giorni_Famiglia_DEPENDEX.pdf')
    generate_guida_pdf(out_file)
