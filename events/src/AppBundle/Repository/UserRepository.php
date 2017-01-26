<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Loads the user for the given username.
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     * @return UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        // TODO: Implement loadUserByUsername() method.
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Loads the user for the given username.
     * This method must return null if the user is not found.
     *
     * @param User $user The user
     * @return true | false
     */
    public function isUserAdmin($user, $community, $em)
    {
        if($community){
            $communityUser = $em->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$user));

            $acceptState = $em->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $communityUserRoles = $em->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'communityRole'=>100, 'state'=>$acceptState));

            if($communityUserRoles){
                return true;
            }

        }
        return false;
    }


    /**
     *
     * İlgili kullanıcının verilen topluluk için admin role sahip olup olmadığı sonucunu döner. Topluluğun onaylanmış
     * ya da yayından kaldırılmış olup olmadığına bakılmaz.
     *
     * @param User $user The user
     * @param Community $community The community
     * @return true | false
     */
    public function isUserCommunityAdmin($user, $community)
    {
        if(isset($user) && isset($community)){
            $communityUser = $this->getEntityManager()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$user));

            $acceptState = $this->getEntityManager()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $communityUserRoles = $this->getEntityManager()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'communityRole'=>100, 'state'=>$acceptState));

            if($communityUserRoles){
                return true;
            }
        }
        return false;
    }

}
