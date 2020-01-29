<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageBuilder\Model\Dom;

use Gt\Dom\HTMLDocument as GtDomHTMLDocument;
use Magento\Framework\ObjectManagerInterface;
use Magento\PageBuilder\Model\Dom\Adapter\ElementInterface;
use Magento\PageBuilder\Model\Dom\Adapter\HtmlCollectionInterface;
use Magento\PageBuilder\Model\Dom\Adapter\HtmlDocumentInterface;

/**
 * PhpGt DOM HTMLDocument wrapper.
 */
class HtmlDocument implements HtmlDocumentInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GtDomHTMLDocument
     */
    private $document;

    /**
     * HtmlDocument constructor.
     * @param ObjectManagerInterface $objectManager
     * @param string $document
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        string $document = ""
    ) {
        $this->objectManager = $objectManager;
        $this->document = $this->objectManager->create(GtDomHTMLDocument::class, [ 'document' => $document ]);
    }

    /**
     * @inheritDoc
     */
    public function querySelector(string $selector): ElementInterface
    {
        return $this->objectManager->create(
            ElementInterface::class,
            [ 'element' => $this->document->querySelector($selector) ]
        );
    }

    /**
     * @inheritDoc
     */
    public function querySelectorAll(string $selector): HtmlCollectionInterface
    {
        return $this->objectManager->create(
            HtmlCollectionInterface::class,
            [ 'collection' => $this->document->querySelectorAll($selector) ]
        );
    }

    /**
     * @inheritDoc
     */
    public function saveHTML(): string
    {
        return $this->document->saveHTML();
    }

    /**
     * @inheritDoc
     */
    public function getElementsByClassName(string $names): HtmlCollectionInterface
    {
        return $this->objectManager->create(
            HtmlCollectionInterface::class,
            [ 'collection' => $this->document->getElementsByClassName($names) ]
        );
    }
}
