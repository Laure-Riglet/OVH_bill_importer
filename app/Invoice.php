<?php

require 'Database.php';

class Invoice
{
    private $id;
    private $service;
    private $originalId;
    private $fileName;
    private $filePath;
    private $issued_at;
    private $priceWithoutTax;
    private $priceWithTax;
    private $pdfUrl;

    /**
     * Find all invoices
     *
     * @return array
     */
    public function findAll(): array
    {
        return Database::getPDO()
            ->query('SELECT * FROM invoices')
            ->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * Find an invoice by its original ID
     *
     * @param string $originalId
     * @return Invoice|bool
     */
    static public function findByOriginalId($originalId)
    {
        return Database::getPDO()
            ->query('SELECT * FROM invoices WHERE `original_id` = "' . $originalId . '"')
            ->fetchObject(self::class);
    }

    /**
     * Check if an invoice already exists in the database
     * @return bool
     */
    static public function exists(string $originalId): bool
    {
        return !empty(self::findByOriginalId($originalId));
    }

    /**
     * Find service the invoice belongs to
     * @return string
     */
    static public function getServiceLongName($serviceShortName): string
    {
        $services = [
            'OVH' => 'OVH',
            'SYS' => 'SoYouStart',
            'KIM' => 'Kimsufi'
        ];

        return $services[$serviceShortName];
    }

    /**
     * Insert an invoice into the database
     * @return void
     */
    public function insert(): void
    {
        $query = 'INSERT INTO invoices (
                service, original_id, filename, filepath, issued_at, price_without_tax, price_with_tax, pdf_url
            ) 
            VALUES (
                :service, :original_id, :filename, :filepath, :issued_at, :price_without_tax, :price_with_tax, :pdf_url
            )';
        $statement = Database::getPDO()->prepare($query);
        $statement->execute([
            'service' => $this->getService(),
            'original_id' => $this->getOriginalId(),
            'filename' => $this->getFileName(),
            'filepath' => $this->getFilePath(),
            'issued_at' => $this->getIssuedAt(),
            'price_without_tax' => $this->getPriceWithoutTax(),
            'price_with_tax' => $this->getPriceWithTax(),
            'pdf_url' => $this->getpdfUrl()
        ]);
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the value of service
     */
    public function setService(string $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Get the value of originalId
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * Set the value of originalId
     */
    public function setOriginalId(string $originalId): self
    {
        $this->originalId = $originalId;
        return $this;
    }

    /**
     * Get the value of fileName
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the value of fileName
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get the value of filePath
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set the value of filePath
     */
    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Get the value of issued_at
     */
    public function getIssuedAt()
    {
        return $this->issued_at;
    }

    /**
     * Set the value of issued_at
     */
    public function setIssuedAt(string $issued_at): self
    {
        $this->issued_at = $issued_at;
        return $this;
    }

    /**
     * Get the value of priceWithoutTax
     */
    public function getPriceWithoutTax()
    {
        return $this->priceWithoutTax;
    }

    /**
     * Set the value of priceWithoutTax
     */
    public function setPriceWithoutTax(float $priceWithoutTax): self
    {
        $this->priceWithoutTax = $priceWithoutTax;
        return $this;
    }

    /**
     * Get the value of priceWithTax
     */
    public function getPriceWithTax()
    {
        return $this->priceWithTax;
    }

    /**
     * Set the value of priceWithTax
     */
    public function setPriceWithTax(float $priceWithTax): self
    {
        $this->priceWithTax = $priceWithTax;
        return $this;
    }

    /**
     * Get the value of pdfUrl
     */
    public function getPdfUrl()
    {
        return $this->pdfUrl;
    }

    /**
     * Set the value of pdfUrl
     */
    public function setPdfUrl(string $pdfUrl): self
    {
        $this->pdfUrl = $pdfUrl;
        return $this;
    }
}
