<?php

namespace App\Service;

use Smalot\PdfParser\Parser;

class PdfExtractor
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extrait du texte brut depuis un fichier PDF
     */
    public function extractText(string $filePath): string
    {
        $pdf = $this->parser->parseFile($filePath);
        return $pdf->getText();
    }

    /**
     * Essaie d'extraire des données structurées (adresse, commande, client, etc.)
     */
    public function extractData(string $text): array
    {
        $data = [];

        // Exemple : numéro de commande
        if (preg_match('/Commande\s*#?\s*(\d+)/i', $text, $m)) {
            $data['numeroCommande'] = trim($m[1]);
        }

        // Exemple : adresse (très simple, à adapter à ta structure PDF)
        if (preg_match('/Adresse\s*:\s*(.+)/i', $text, $m)) {
            $data['adresse'] = trim($m[1]);
        }

        // Exemple : nom du client
        if (preg_match('/Client\s*:\s*(.+)/i', $text, $m)) {
            $data['client'] = trim($m[1]);
        }

        return $data;
    }
}
