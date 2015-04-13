<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace OApigility\Hydrator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineObjectHydratorFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $options       = (array) @$serviceLocator->get('Config')['o-apigility']['apigility_doctrine_hydrator'];
        
        $namingStrategy = isset($options['naming_strategy']) ? $options['naming_strategy'] : null;
        if ($namingStrategy !== null) {
            $namingStrategy = $serviceLocator->get($options['naming_strategy']);
        }
        
        $formatters = isset($options['formatters']) ? $options['formatters'] : null;
        if ($formatters !== null) {
            foreach ($formatters as $key => &$formatter) {
                if (is_array($formatter)) {
                    
                }else{
                    $formatter = $serviceLocator->get($formatter);
                }
            }
        }else{
            $formatters = [];
        }
        
        $filters = isset($options['filters']) ? $options['filters'] : null;
        if ($filters !== null) {
            foreach ($filters as $key => &$filter) {
                if (is_array($filter)) {
                    
                }else{
                    $filter = $serviceLocator->get($filter);
                }
            }
        }else{
            $filters = [];
        }
        if($this->hydrator === null)
        $this->hydrator = new DoctrineObject($namingStrategy, $formatters, $filters, $serviceLocator->get('doctrine.entitymanager.orm_default'), true);
        return $this->hydrator;
    }
    protected $hydrator = null;
}
