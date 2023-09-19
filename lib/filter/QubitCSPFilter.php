<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitCSP extends sfFilter
{
    public function execute($filterChain)
    {
        // Only use CSP if theme is b5.
        if (sfConfig::get('app_b5_theme', false)) {
            $cspResponseHeader = sfConfig::get('app_csp_response_header', '');

            if (empty($cspResponseHeader)) {
                // CSP is deactivated.
                $filterChain->execute();

                return;
            }

            $context = $this->getContext();
            if (false === array_search($cspResponseHeader, ['Content-Security-Policy-Report-Only', 'Content-Security-Policy'])) {
                $context->getLogger()->err(
                    sprintf(
                        'Setting \'app_csp_response_header\' is not set properly. CSP is not being used.'
                    )
                );

                $filterChain->execute();

                return;
            }

            $cspDirectives = sfConfig::get('app_csp_directives', '');
            if (empty($cspDirectives)) {
                $context->getLogger()->err(
                    sprintf(
                        'Setting \'app_csp_directives\' is not set properly. CSP is not being used.'
                    )
                );

                $filterChain->execute();

                return;
            }

            $nonce = $this->getRandomNonce();
            // Set response header.
            $context->response->setHttpHeader(
                $cspResponseHeader,
                $cspDirectives = str_replace('nonce', 'nonce-'.$nonce, $cspDirectives)
            );
            // Save for use in templates.
            sfConfig::set('csp_nonce', 'nonce='.$nonce);
        }

        $filterChain->execute();
    }

    protected function getRandomNonce($length = 32)
    {
        $string = md5(rand());

        return substr($string, 0, $length);
    }
}
