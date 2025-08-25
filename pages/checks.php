<?php

$content = <<<TEXTOSTERONE

Diese Zusammenstellung von Tools und Ressourcen dient dazu, die Sicherheit und Leistung von Websites zu überprüfen und zu verbessern. Jedes Tool bietet spezifische Einblicke und Hilfestellungen, um Webseiten nicht nur sicherer, sondern auch benutzerfreundlicher und performanter zu gestalten.

### Mozilla Observatory
[Observatory by Mozilla](https://observatory.mozilla.org/analyze/redaxo.org) bietet eine umfassende Analyse der Sicherheitsmaßnahmen einer Website. Es bewertet, wie gut eine Seite gegenüber gängigen Webangriffen geschützt ist, und gibt Empfehlungen zur Verbesserung der Sicherheitskonfiguration.

### W3C Validator
Der [W3C Markup Validation Service](https://validator.w3.org/) prüft die Markup-Konformität von Webdokumenten mit Webstandards. Korrektes HTML und CSS sind grundlegend für die Sicherheit und Zugänglichkeit einer Webseite.

### Google PageSpeed Insights
[PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/?hl=de) von Google analysiert den Inhalt einer Webseite und gibt Empfehlungen, um deren Ladezeit zu verbessern. Schnell ladende Seiten verbessern nicht nur das Nutzererlebnis, sondern auch die Sicherheit durch reduzierte Ladezeitabhängigkeiten.

### Seobility SEO Check
Der [Seobility SEO Check](https://www.seobility.net/de/seocheck/) untersucht Webseiten auf allgemeine SEO-Probleme und gibt Tipps zur Optimierung für Suchmaschinen. Eine gut optimierte Seite erhöht die Sichtbarkeit und kann indirekt zur Sicherheit beitragen, indem sie gute Praktiken fördert.

### Backlinktest Dead Link Checker
[Backlinktest's Dead Link Checker](https://www.backlinktest.com/deadlink.php) identifiziert tote Links auf einer Webseite. Tote Links können ein Sicherheitsrisiko darstellen und die Glaubwürdigkeit einer Webseite untergraben.

### SSL Labs
[SSL Labs' SSL Test](https://www.ssllabs.com/ssltest/analyze.html?d=redaxo.org) überprüft die Qualität der SSL/TLS-Konfiguration einer Website. Eine starke Konfiguration ist essenziell, um den Datenverkehr zwischen Nutzern und der Webseite zu schützen.

### Why No Padlock?
[Why No Padlock?](https://www.whynopadlock.com/) hilft dabei, Probleme zu identifizieren, die verhindern, dass eine Webseite als vollständig sicher eingestuft wird. Es überprüft auf unsichere Links, fehlende Sicherheitsheader und andere Sicherheitslücken.

### web.dev Measure
[web.dev Measure](https://web.dev/measure/) bietet eine umfassende Analyse für Performance, Zugänglichkeit, Best Practices und SEO. Es gibt klare Anweisungen zur Verbesserung der Webseite in diesen Schlüsselbereichen.

### Web Accessibility Checklist
[Web Accessibility Checklist](https://webaccessibilitychecklist.com/) und [The A11Y Project Checklist](https://www.a11yproject.com/checklist/) bieten Richtlinien und Prüfpunkte, um die Zugänglichkeit einer Webseite zu gewährleisten. Barrierefreiheit ist ein wichtiger Aspekt der Web-Sicherheit und -Inklusion.

### CookieMetrix
[CookieMetrix](https://www.cookiemetrix.com/) analysiert die Cookie-Nutzung einer Webseite und hilft dabei, die Einhaltung der Datenschutz-Grundverordnung (DSGVO) zu verstehen. Datenschutz und Sicherheit gehen Hand in Hand bei der Gewährleistung einer sicheren Webumgebung.

Diese Tools zusammen bieten einen umfassenden Ansatz, um verschiedene Aspekte der Web-Sicherheit, Performance und Zugänglichkeit zu überprüfen und zu verbessern. Durch die regelmäßige Anwendung dieser Ressourcen können Entwickler und Website-Betreiber ihre Seiten effektiv schützen und optimieren.
TEXTOSTERONE;

$content = str_replace('<a href="', '<a target="_blank" rel="noopener noreferrer" href="', rex_markdown::factory()->parse($content));

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('security_checks'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
