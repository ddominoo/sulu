<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\CoreBundle\Build;

/**
 * Builder for initializing PHPCR.
 */
class PhpcrBuilder extends SuluBuilder
{
    public function getName()
    {
        return 'phpcr';
    }

    public function getDependencies()
    {
        return ['database'];
    }

    public function build()
    {
        $command = 'sulu:document:initialize';
        $options = [];

        // Drop existing data if this is a destroying invocation
        if ($this->input->getOption('destroy')) {
            $options = [
                '--force' => true,
                '--purge' => true,
            ];
        }

        // Initialize Sulu node types
        $this->execCommand('Initializing Sulu document manager', $command, $options);
    }
}
