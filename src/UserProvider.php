<?php

namespace robrogers3\laradauth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use RuntimeException;

/**
 * @author  Lucas Vasconcelos <lucas@vscn.co>
 * @package LSV\LDAP
 */
class UserProvider implements UserProviderContract
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
     * Create new hybrid user provider
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @param  string $host
     * @return void
     */
    public function __construct(HasherContract $hasher, $model, $host, $domain)
    {
        $this->hasher = $hasher;
        $this->model = $model;
        $this->host = $host;
        $this->domain = $domain;
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

        // Append the domain name to user's credentials.
        // That allow the user to type just the username.
        /*        if (false === strpos($credentials['email'], '@')) {
                    $credentials['email'] .= '@' . $this->domain;
                }*/

        $query->where('email', $credentials['email']);

        $user = $query->first();
        if ($user) {
            return $user;
        }
        $user = User::make($credentials);

        return $user;
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
        // If user can't be authenticated by LDAP,
        // try the password stored into 'users' table.
        if (! $this->checkLdapCredentials($user, $credentials)) {

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
    public function checkLdapCredentials(Authenticatable $user, array $credentials)
    {

        $handler = @ldap_connect($this->host);

        if (! $handler) {
            throw new RuntimeException("Connection fail! Check your server address: '{$this->host}'.");
        }

        @ldap_set_option($handler, LDAP_OPT_PROTOCOL_VERSION, 3);

        $username = strtok($user->email, '@');
        $rdn = $this->makeRdn($username);

        $bind = @ldap_bind($handler, $rdn, $credentials['password']);

        @ldap_close($handler);
        unset($handler);
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
        $parts = explode('.', $this->domain);
        //uid=USERNAME,cn=users,dc=HOSTNAME,dc=DOMAIN,dc=com
        return sprintf('uid=%s,%s', $username, $this->domain);
    }
}
