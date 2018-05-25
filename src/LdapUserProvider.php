<?php

namespace robrogers3\laradauth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LdapUserProvider implements UserProviderContract
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Address to your LDAP server.
     *
     * @var string
     */
    protected $host;

    /**
     * Your domain name.
     *
     * @var string
     */
    protected $domain;
    /**
     * @var
     */
    private $base_dn;
    /**
     * @var
     */
    private $user_dn;

    /**
     * Create new hybrid user provider
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param  string $model
     * @param  string $host
     * @param string $domain
     * @param string $basedn
     * @param string $userdn
     */
    public function __construct(HasherContract $hasher, $model, $host, $domain, $base_dn, $user_dn)
    {
        $this->hasher = $hasher;
        $this->model = $model;
        $this->host = $host;
        $this->domain = $domain;
        $this->base_dn = $base_dn;
        $this->user_dn = $user_dn;
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where($model->getRememberTokenName(), $token)
            ->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);

        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {

        if (empty($credentials)) {
            return null;
        }

        $query = $this->createModel()->newQuery();

        // Append the domain name to user's credentials if not set
        // stores the email in the DB for potential usage like notifications.
        if (false === strpos($credentials['email'], '@')) {
            $credentials['email'] .= '@' . $this->domain;
        }

        $query->where('email', $credentials['email']);

        $model = $query->first();

        if ($model) {
            return $model;
        }

        $model = $this->makeModel($credentials);

        return $model;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        //TODO determine if we even want to store the passowrd.
        if (! $this->authenticate($user, $credentials)) {

            $plain = $credentials['password'];

            return $this->hasher->check($plain, $user->getAuthPassword());
        }
        return true;
    }

    /**
     * Check user's credentials against LDAP server.
     *
     * @param  Authenticatable $user
     * @param  array           $credentials
     * @return bool
     */
    public function authenticate(Authenticatable $user, array $credentials)
    {

        $handler = ldap_connect($this->host);

        if (! $handler) {
            throw new RuntimeException("Connection fail! Check your server address: '{$this->host}'.");
        }

        try {
            ldap_set_option($handler, LDAP_OPT_PROTOCOL_VERSION, 3);
        } catch (\ErrorException $e) {
            ;
        }

        $username = strtok($user->email, '@');

        $rdn = $this->makeRdn($username);

        try {
            $bind = ldap_bind($handler, $rdn, $credentials['password']);
        } catch (\ErrorException $e) {
            $bind = false;
        }

        if ($handler) {
            ldap_close($handler);

            unset($handler);
        }

        if ($bind) {
            $user->save();
        }

        return $bind;
    }

    /**
     * Make the RDN string.
     *
     * @param  string $username
     * @return string
     */
    protected function makeRdn($username)
    {
        //uid=USERNAME,cn=users,dc=HOSTNAME,dc=DOMAIN,dc=com
        //cn=USERNAME ...
        return sprintf('%s=%s,%s', $this->user_dn, $username, $this->base_dn);
    }

    private function makeModel($credentials)
    {

        $model  = $this->createModel();

        $userName = explode('@', $credentials['email'])[0];

        $name = ucwords(str_replace('.', ' ', $userName));;

        $credentials['password'] = Hash::make($credentials['password']);

        $attributes = ['name' => $name];

        if (config('services.ldap.create-user-name')) {
            $attributes['user_name'] = $userName;
        }

        $attributes = array_merge($attributes, $credentials);

        $model->fill(
            $attributes
        );

        return $model;
    }
}
