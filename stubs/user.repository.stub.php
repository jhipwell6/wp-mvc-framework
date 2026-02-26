<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\Users\{{user.class}}\Generated;

use {{app_namespace}}\Domain\Users\{{user.class}}\{{user.class}};
use Snowberry\WpMvc\Contracts\UserDTO;
use Snowberry\WpMvc\Contracts\UserMetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\UserRepositoryInterface;
use Snowberry\WpMvc\Domain\Persistence\AbstractUserRepository;

/**
 * @extends AbstractUserRepository<{{user.class}}>
 */
class {{user.class}}RepositoryBase extends AbstractUserRepository
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        private UserMetaRepositoryInterface $userMetaRepository,
    ) {
        parent::__construct($userRepository);
    }

    protected function map(UserDTO $user): {{user.class}}
    {
        return new {{user.class}}(
            id: $user->ID,
            email: $user->user_email,
            login: $user->user_login,
            displayName: $user->display_name,
            roles: $user->roles,
            caps: $user->caps,
{{user.field_hydration}}
        );
    }

    protected function extractUserId(object $entity): ?int
    {
        /** @var {{user.class}} $entity */
        return $entity->id;
    }

    protected function extractUserData(object $entity): array
    {
        /** @var {{user.class}} $entity */
        return [
            'user_email' => $entity->email,
            'user_login' => $entity->login,
            'display_name' => $entity->displayName,
            'role' => $entity->roles[0] ?? '',
        ];
    }

    public function save(object $entity): object
    {
        /** @var {{user.class}} $entity */
        $userId = $this->extractUserId($entity);
        $data = $this->extractUserData($entity);

        if ($userId === null) {
            $userId = $this->userRepository->insert($data);
        } else {
            $this->userRepository->update($userId, $data);
        }

        $this->saveMeta($userId, $entity);

        $reloaded = $this->find($userId);

        if ($reloaded === null) {
            throw new \RuntimeException('Unable to reload user entity after save.');
        }

        return $reloaded;
    }

    protected function saveMeta(int $userId, object $entity): void
    {
        /** @var {{user.class}} $entity */
{{user.field_meta_persistence}}
    }
}
