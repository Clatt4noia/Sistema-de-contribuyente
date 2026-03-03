<?php

namespace App\Domains\Billing\Services\Builders;

use Greenter\Xml\Builder\DespatchBuilder;
use Greenter\Model\DocumentInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Greenter\Xml\Filter\FormatFilter;
use Twig\TwigFilter;
use Greenter\Model\Despatch\Despatch;

class CustomDespatchBuilder extends DespatchBuilder
{
    public function __construct(array $options = [])
    {
        // Use local resources path for templates
        $loader = new FilesystemLoader(resource_path('views/xml/templates'));
        $this->twig = new Environment($loader, $options);

        // Add standard filters required by Greenter templates
        $formatFilter = new FormatFilter();
        $this->twig->addFilter(new TwigFilter('n_format', [$formatFilter, 'number']));
        $this->twig->addFilter(new TwigFilter('n_format_limit', [$formatFilter, 'numberLimit']));
    }

    public function build(DocumentInterface $document): ?string
    {
        /** @var Despatch $despatch */
        $despatch = $document;
        // Force using our custom transportista template
        return $this->render('despatch_transportista.xml.twig', $document);
    }
}
