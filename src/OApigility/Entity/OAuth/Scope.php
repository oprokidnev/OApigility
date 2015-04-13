<?php

namespace OApigility\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scopes
 *
 * @ORM\Table(name="oauth_scopes")
 * @ORM\Entity
 */
class Scope
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=2000, nullable=true)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=80, nullable=true)
     */
    private $clientId;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_default", type="smallint", nullable=true)
     */
    private $isDefault;


}

