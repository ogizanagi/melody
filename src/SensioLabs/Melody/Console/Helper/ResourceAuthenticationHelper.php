<?php

namespace SensioLabs\Melody\Console\Helper;

use SensioLabs\Melody\Exception\InvalidCredentialsException;
use SensioLabs\Melody\Handler\AuthenticationHandlerInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Maxime STEINHAUSSER <maxime.steinhausser@gmail.com>
 */
class ResourceAuthenticationHelper extends Helper
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'resource_authentication';
    }

    public function askCredentials(InputInterface $input, OutputInterface $output, AuthenticationHandlerInterface $authenticationHandler)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        for (;;) {
            $credentials = [];
            $output->writeln('Authentication required. Please, provide the following informations:');
            foreach ($this->getRequiredCredentials($authenticationHandler) as $name => $type) {
                $question = new Question(sprintf('<fg=yellow>%s:</> ', $name));
                $question->setHidden(AuthenticationHandlerInterface::CREDENTIALS_SECRET === $type);
                $credentials[$name] = $questionHelper->ask($input, $output, $question);
            }

            try {
                return $authenticationHandler->authenticate($credentials);
                break;
            } catch (InvalidCredentialsException $e) {
                $output->writeln(sprintf('<error>Something wrong happened: %s.</error>', $e->getMessage()));
            }
        }
    }

    private function getRequiredCredentials(AuthenticationHandlerInterface $authenticationHandler)
    {
        $requiredCredentials = $authenticationHandler->getRequiredCredentials();

        if (!ctype_digit(implode('', array_keys($requiredCredentials)))) {
            return $requiredCredentials;
        }

        return $requiredCredentials = array_map(function () {
            return AuthenticationHandlerInterface::CREDENTIALS_NORMAL;
        }, array_flip($requiredCredentials));
    }
}
