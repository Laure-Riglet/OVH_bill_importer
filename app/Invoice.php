<?php

require_once 'Database.php';

class Invoice
{
    private $id;
    private $service;
    private $original_id;
    private $filename;
    private $filepath;
    private $issued_at;
    private $price_without_tax;
    private $price_with_tax;
    private $pdf_url;

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
     * @param string $original_id
     * @return Invoice|bool
     */
    static public function findByOriginalId($original_id)
    {
        return Database::getPDO()
            ->query('SELECT * FROM invoices WHERE `original_id` = "' . $original_id . '";')
            ->fetchObject(self::class);
    }

    /**
     * Check if an invoice already exists in the database
     * @return bool
     */
    static public function exists(string $original_id): bool
    {
        return !empty(self::findByOriginalId($original_id));
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
            'filename' => $this->getFilename(),
            'filepath' => $this->getFilepath(),
            'issued_at' => $this->getIssuedAt(),
            'price_without_tax' => $this->getPriceWithoutTax(),
            'price_with_tax' => $this->getPriceWithTax(),
            'pdf_url' => $this->getPdfUrl()
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
     * Get the value of original_id
     */
    public function getOriginalId()
    {
        return $this->original_id;
    }

    /**
     * Set the value of original_id
     */
    public function setOriginalId(string $original_id): self
    {
        $this->original_id = $original_id;
        return $this;
    }

    /**
     * Get the value of filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the value of filename
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get the value of filepath
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set the value of filepath
     */
    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;
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
     * Get the value of price_without_tax
     */
    public function getPriceWithoutTax()
    {
        return $this->price_without_tax;
    }

    /**
     * Set the value of price_without_tax
     */
    public function setPriceWithoutTax(float $price_without_tax): self
    {
        $this->price_without_tax = $price_without_tax;
        return $this;
    }

    /**
     * Get the value of price_with_tax
     */
    public function getPriceWithTax()
    {
        return $this->price_with_tax;
    }

    /**
     * Set the value of price_with_tax
     */
    public function setPriceWithTax(float $price_with_tax): self
    {
        $this->price_with_tax = $price_with_tax;
        return $this;
    }

    /**
     * Get the value of pdf_url
     */
    public function getPdfUrl()
    {
        return $this->pdf_url;
    }

    /**
     * Set the value of pdf_url
     */
    public function setPdfUrl(string $pdf_url): self
    {
        $this->pdf_url = $pdf_url;
        return $this;
    }
}
