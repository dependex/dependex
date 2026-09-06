"""
Motore di Rendering Template HTML Responsive con Design System Luxury Dark & Gold.
Sostituisce i segnaposto dinamici, compila il footer legale GDPR e garantisce conformità visiva.
"""

from typing import Dict, Any

class TemplateRenderer:
    HEADER_MARKUP = """<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ subject }}</title>
  <style>
    body { margin: 0; padding: 0; background-color: #07090e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0; }
    .wrapper { width: 100%; max-width: 600px; margin: 0 auto; background-color: #0d121f; border: 1px solid #1e293b; border-radius: 12px; overflow: hidden; }
    .header { background: linear-gradient(135deg, #0b0f19 0%, #172033 100%); padding: 32px 24px; text-align: center; border-bottom: 2px solid #d99a26; }
    .brand-title { font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #f8fafc; margin: 0; text-transform: uppercase; }
    .brand-sub { font-size: 11px; letter-spacing: 3px; color: #d99a26; margin-top: 6px; text-transform: uppercase; }
    .content { padding: 36px 28px; line-height: 1.7; font-size: 15px; color: #cbd5e1; }
    .cta-btn { display: inline-block; background: linear-gradient(135deg, #d99a26 0%, #b87d16 100%); color: #07090e !important; text-decoration: none; padding: 14px 28px; font-weight: 700; border-radius: 8px; margin: 24px 0; text-align: center; }
    .footer { background-color: #090d16; padding: 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #1e293b; }
    .footer a { color: #94a3b8; text-decoration: underline; }
  </style>
</head>
<body>
  <div style="padding: 20px 10px;">
    <div class="wrapper">
      <div class="header">
        <div class="brand-title">DEPENDEX · CLUB</div>
        <div class="brand-sub">SISTEMA DI ECCELLENZA & AUTONOMIA</div>
      </div>
      <div class="content">
"""

    FOOTER_MARKUP = """
      </div>
      <div class="footer">
        <p>Ricevi questa comunicazione perché hai espresso consenso o hai partecipato alle iniziative Dependex Club.</p>
        <p>Dependex Ecosystem · Governance & Soluzioni Avanzate</p>
        <p>
          <a href="{{ unsubscribe_url }}">Disiscrizione immediata</a> · 
          <a href="{{ preferences_url }}">Gestione Preferenze</a> · 
          <a href="https://dependex.social/privacy">Informativa Privacy</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
"""

    def render(self, body_template: str, variables: Dict[str, Any]) -> str:
        """Compila il template completo iniettando i dati variabili."""
        rendered_body = body_template
        for key, val in variables.items():
            placeholder = f"{{{{ {key} }}}}"
            placeholder_no_space = f"{{{{{key}}}}}"
            rendered_body = rendered_body.replace(placeholder, str(val))
            rendered_body = rendered_body.replace(placeholder_no_space, str(val))

        full_html = self.HEADER_MARKUP + rendered_body + self.FOOTER_MARKUP
        
        # Sostituzione delle variabili di sistema nel wrapper e footer
        for key, val in variables.items():
            placeholder = f"{{{{ {key} }}}}"
            placeholder_no_space = f"{{{{{key}}}}}"
            full_html = full_html.replace(placeholder, str(val))
            full_html = full_html.replace(placeholder_no_space, str(val))

        return full_html
