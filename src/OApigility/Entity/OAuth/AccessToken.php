<?php

namespace OApigility\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

if (!in_array('OApigility\Entity\OAuth\AccessToken', get_declared_classes())) {

    /**
     * AccessTokens
     *
     * @ORM\Table(name="oauth_access_tokens", indexes={@ORM\Index(name="IDX_CA42527CA76ED395", columns={"user_id"})})
     * @ORM\Entity
     */
    class AccessToken
    {

        /**
         * @var string
         *
         * @ORM\Column(name="access_token", type="string", length=40, nullable=false)
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $accessToken;

        /**
         * @var string
         *
         * @ORM\Column(name="client_id", type="string", length=80, nullable=false)
         */
        private $clientId;

        /**
         * @var string
         *
         * @ORM\Column(name="expires", type="string", length=255, nullable=false)
         */
        private $expires;

        /**
         * @var string
         *
         * @ORM\Column(name="scope", type="string", length=2000, nullable=true)
         */
        private $scope;

        /**
         * @var \OApigility\Entity\OAuth\User
         *
         * @ORM\ManyToOne(targetEntity="OApigility\Entity\OAuth\User")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="user_id", referencedColumnName="username")
         * })
         */
        private $user;

    }

}