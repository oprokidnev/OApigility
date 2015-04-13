<?php

namespace OApigility\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jwt
 *
 * @ORM\Table(name="oauth_jwt")
 * @ORM\Entity
 */
class Jwt
{
    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=80, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=80, nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="public_key", type="string", length=2000, nullable=true)
     */
    private $publicKey;


}

