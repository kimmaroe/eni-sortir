<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['cancel', 'view', 'register'])
            && $subject instanceof \App\Entity\Event;
    }

    /**
     * @param string $attribute
     * @param Event $event
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $event, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {

            //dans le cas où l'utilisateur tente de s'iscrire à une sortie...
            case 'register':
                //la sortie doit être ouverte pour s'inscrire
                if ($event->getState()->getName() !== EventState::OPEN){
                    return false;
                }
                //il doit y avoir des places dispos
                if ($event->getRegistrations()->count() >= $event->getMaxRegistrations()){
                    return false;
                }
                return true;
                break;

            //dans le cas où l'utilisateur tente d'annuler une sortie...
            case 'cancel':
                //seulement si c'est le créateur ou un admin
                if($user === $event->getCreator() || $user->isAdmin()){
                    //et que la sortie est actuellement ouverture ou clôturée
                    if (in_array($event->getState()->getName(), [EventState::OPEN, EventState::CLOSED])){
                        return true;
                    }
                }
                return false;
                break;

            //dans le cas où l'utilisateur tente de voir le détail d'une sortie
            case 'view':
                if ($user->isAdmin() || $user === $event->getCreator()){
                    return true;
                }
                //on ne peut voir une sortie que si elle n'est ni en état "créée" ou archivée
                return !in_array($event->getState()->getName(), [EventState::CREATED, EventState::ARCHIVED]);
                break;
        }

        return false;
    }
}
