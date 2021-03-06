@extends('documents.laravel53.layout')

@section('content')
<article>
    <h1>Redis</h1>
    <ul>
        <li><a href="#introduction">Introduction</a>
            <ul>
                <li><a href="#configuration">Configuration</a>
                </li>
            </ul>
        </li>
        <li><a href="#interacting-with-redis">Interacting With Redis</a>
            <ul>
                <li><a href="#pipelining-commands">Pipelining Commands</a>
                </li>
            </ul>
        </li>
        <li><a href="#pubsub">Pub / Sub</a>
        </li>
    </ul>
    <p>
        <a name="introduction"></a>
    </p>
    <h2><a href="#introduction">Introduction</a></h2>
    <p><a href="http://redis.io">Redis</a> is an open source, advanced key-value store. It is often referred to as a data structure server since keys can contain <a href="http://redis.io/topics/data-types#strings">strings</a>, <a href="http://redis.io/topics/data-types#hashes">hashes</a>, <a href="http://redis.io/topics/data-types#lists">lists</a>, <a href="http://redis.io/topics/data-types#sets">sets</a>, and <a href="http://redis.io/topics/data-types#sorted-sets">sorted sets</a>. Before using Redis with Laravel, you will need to install the <code class=" language-php">predis<span class="token operator">/</span>predis</code> package via Composer:</p>
    <pre class=" language-php"><code class=" language-php">composer <span class="token keyword">require</span> predis<span class="token operator">/</span>predis</code></pre>
    <p>
        <a name="configuration"></a>
    </p>
    <h3>Configuration</h3>
    <p>The Redis configuration for your application is located in the <code class=" language-php">config<span class="token operator">/</span>database<span class="token punctuation">.</span>php</code> configuration file. Within this file, you will see a <code class=" language-php">redis</code> array containing the Redis servers utilized by your application:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token string">'redis'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token punctuation">[</span>

    <span class="token string">'cluster'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token boolean">false</span><span class="token punctuation">,</span>

    <span class="token string">'default'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token punctuation">[</span>
        <span class="token string">'host'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token string">'127.0.0.1'</span><span class="token punctuation">,</span>
        <span class="token string">'port'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token number">6379</span><span class="token punctuation">,</span>
        <span class="token string">'database'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token number">0</span><span class="token punctuation">,</span>
    <span class="token punctuation">]</span><span class="token punctuation">,</span>

<span class="token punctuation">]</span><span class="token punctuation">,</span></code></pre>
    <p>The default server configuration should suffice for development. However, you are free to modify this array based on your environment. Each Redis server defined in your configuration file is required to have a name, host, and port.</p>
    <p>The <code class=" language-php">cluster</code> option will instruct the Laravel Redis client to perform client-side sharding across your Redis nodes, allowing you to pool nodes and create a large amount of available RAM. However, note that client-side sharding does not handle failover; therefore, is primarily suited for cached data that is available from another primary data store.</p>
    <p>Additionally, you may define an <code class=" language-php">options</code> array value in your Redis connection definition, allowing you to specify a set of Predis <a href="https://github.com/nrk/predis/wiki/Client-Options">client options</a>.</p>
    <p>If your Redis server requires authentication, you may supply a password by adding a <code class=" language-php">password</code> configuration item to your Redis server configuration array.</p>
    <blockquote class="has-icon note">
        <p>
            <div class="flag"><span class="svg"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" version="1.1" x="0px" y="0px" width="90px" height="90px" viewBox="0 0 90 90" enable-background="new 0 0 90 90" xml:space="preserve"><path fill="#FFFFFF" d="M45 0C20.1 0 0 20.1 0 45s20.1 45 45 45 45-20.1 45-45S69.9 0 45 0zM45 74.5c-3.6 0-6.5-2.9-6.5-6.5s2.9-6.5 6.5-6.5 6.5 2.9 6.5 6.5S48.6 74.5 45 74.5zM52.1 23.9l-2.5 29.6c0 2.5-2.1 4.6-4.6 4.6 -2.5 0-4.6-2.1-4.6-4.6l-2.5-29.6c-0.1-0.4-0.1-0.7-0.1-1.1 0-4 3.2-7.2 7.2-7.2 4 0 7.2 3.2 7.2 7.2C52.2 23.1 52.2 23.5 52.1 23.9z"></path></svg></span>
            </div> If you have the Redis PHP extension installed via PECL, you will need to rename the alias for Redis in your <code class=" language-php">config<span class="token operator">/</span>app<span class="token punctuation">.</span>php</code> file.</p>
    </blockquote>
    <p>
        <a name="interacting-with-redis"></a>
    </p>
    <h2><a href="#interacting-with-redis">Interacting With Redis</a></h2>
    <p>You may interact with Redis by calling various methods on the <code class=" language-php">Redis</code> <a href="/docs/5.3/facades">facade</a>. The <code class=" language-php">Redis</code> facade supports dynamic methods, meaning you may call any <a href="http://redis.io/commands">Redis command</a> on the facade and the command will be passed directly to Redis. In this example, we will call the Redis <code class=" language-php"><span class="token constant">GET</span></code> command by calling the <code class=" language-php">get</code> method on the <code class=" language-php">Redis</code> facade:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token delimiter">&lt;?php</span>

<span class="token keyword">namespace</span> <span class="token package">App<span class="token punctuation">\</span>Http<span class="token punctuation">\</span>Controllers</span><span class="token punctuation">;</span>

<span class="token keyword">use</span> <span class="token package">Illuminate<span class="token punctuation">\</span>Support<span class="token punctuation">\</span>Facades<span class="token punctuation">\</span>Redis</span><span class="token punctuation">;</span>
<span class="token keyword">use</span> <span class="token package">App<span class="token punctuation">\</span>Http<span class="token punctuation">\</span>Controllers<span class="token punctuation">\</span>Controller</span><span class="token punctuation">;</span>

<span class="token keyword">class</span> <span class="token class-name">UserController</span> <span class="token keyword">extends</span> <span class="token class-name">Controller</span>
<span class="token punctuation">{</span>
    <span class="token comment" spellcheck="true">/**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */</span>
    <span class="token keyword">public</span> <span class="token keyword">function</span> <span class="token function">showProfile<span class="token punctuation">(</span></span><span class="token variable">$id</span><span class="token punctuation">)</span>
    <span class="token punctuation">{</span>
        <span class="token variable">$user</span> <span class="token operator">=</span> <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">get<span class="token punctuation">(</span></span><span class="token string">'user:profile:'</span><span class="token punctuation">.</span><span class="token variable">$id</span><span class="token punctuation">)</span><span class="token punctuation">;</span>

        <span class="token keyword">return</span> <span class="token function">view<span class="token punctuation">(</span></span><span class="token string">'user.profile'</span><span class="token punctuation">,</span> <span class="token punctuation">[</span><span class="token string">'user'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token variable">$user</span><span class="token punctuation">]</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
    <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
    <p>Of course, as mentioned above, you may call any of the Redis commands on the <code class=" language-php">Redis</code> facade. Laravel uses magic methods to pass the commands to the Redis server, so simply pass the arguments the Redis command expects:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">set<span class="token punctuation">(</span></span><span class="token string">'name'</span><span class="token punctuation">,</span> <span class="token string">'Taylor'</span><span class="token punctuation">)</span><span class="token punctuation">;</span>

<span class="token variable">$values</span> <span class="token operator">=</span> <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">lrange<span class="token punctuation">(</span></span><span class="token string">'names'</span><span class="token punctuation">,</span> <span class="token number">5</span><span class="token punctuation">,</span> <span class="token number">10</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <p>Alternatively, you may also pass commands to the server using the <code class=" language-php">command</code> method, which accepts the name of the command as its first argument, and an array of values as its second argument:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token variable">$values</span> <span class="token operator">=</span> <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">command<span class="token punctuation">(</span></span><span class="token string">'lrange'</span><span class="token punctuation">,</span> <span class="token punctuation">[</span><span class="token string">'name'</span><span class="token punctuation">,</span> <span class="token number">5</span><span class="token punctuation">,</span> <span class="token number">10</span><span class="token punctuation">]</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <h4>Using Multiple Redis Connections</h4>
    <p>You may get a Redis instance by calling the <code class=" language-php"><span class="token scope">Redis<span class="token punctuation">::</span></span>connection</code> method:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token variable">$redis</span> <span class="token operator">=</span> <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">connection<span class="token punctuation">(</span></span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <p>This will give you an instance of the default Redis server. If you are not using server clustering, you may pass the server name to the <code class=" language-php">connection</code> method to get a specific server as defined in your Redis configuration:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token variable">$redis</span> <span class="token operator">=</span> <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">connection<span class="token punctuation">(</span></span><span class="token string">'other'</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <p>
        <a name="pipelining-commands"></a>
    </p>
    <h3>Pipelining Commands</h3>
    <p>Pipelining should be used when you need to send many commands to the server in one operation. The <code class=" language-php">pipeline</code> method accepts one argument: a <code class=" language-php">Closure</code> that receives a Redis instance. You may issue all of your commands to this Redis instance and they will all be executed within a single operation:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">pipeline<span class="token punctuation">(</span></span><span class="token keyword">function</span> <span class="token punctuation">(</span><span class="token variable">$pipe</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
    <span class="token keyword">for</span> <span class="token punctuation">(</span><span class="token variable">$i</span> <span class="token operator">=</span> <span class="token number">0</span><span class="token punctuation">;</span> <span class="token variable">$i</span> <span class="token operator">&lt;</span> <span class="token number">1000</span><span class="token punctuation">;</span> <span class="token variable">$i</span><span class="token operator">++</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
        <span class="token variable">$pipe</span><span class="token operator">-</span><span class="token operator">&gt;</span><span class="token function">set<span class="token punctuation">(</span></span><span class="token string">"key:$i"</span><span class="token punctuation">,</span> <span class="token variable">$i</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
    <span class="token punctuation">}</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <p>
        <a name="pubsub"></a>
    </p>
    <h2><a href="#pubsub">Pub / Sub</a></h2>
    <p>Laravel provides a convenient interface to the Redis <code class=" language-php">publish</code> and <code class=" language-php">subscribe</code> commands. These Redis commands allow you to listen for messages on a given "channel". You may publish messages to the channel from another application, or even using another programming language, allowing easy communication between applications and processes.</p>
    <p>First, let's setup a channel listener using the <code class=" language-php">subscribe</code> method. We'll place this method call within an <a href="/docs/5.3/artisan">Artisan command</a> since calling the <code class=" language-php">subscribe</code> method begins a long-running process:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token delimiter">&lt;?php</span>

<span class="token keyword">namespace</span> <span class="token package">App<span class="token punctuation">\</span>Console<span class="token punctuation">\</span>Commands</span><span class="token punctuation">;</span>

<span class="token keyword">use</span> <span class="token package">Illuminate<span class="token punctuation">\</span>Console<span class="token punctuation">\</span>Command</span><span class="token punctuation">;</span>
<span class="token keyword">use</span> <span class="token package">Illuminate<span class="token punctuation">\</span>Support<span class="token punctuation">\</span>Facades<span class="token punctuation">\</span>Redis</span><span class="token punctuation">;</span>

<span class="token keyword">class</span> <span class="token class-name">RedisSubscribe</span> <span class="token keyword">extends</span> <span class="token class-name">Command</span>
<span class="token punctuation">{</span>
    <span class="token comment" spellcheck="true">/**
     * The name and signature of the console command.
     *
     * @var string
     */</span>
    <span class="token keyword">protected</span> <span class="token variable">$signature</span> <span class="token operator">=</span> <span class="token string">'redis:subscribe'</span><span class="token punctuation">;</span>

    <span class="token comment" spellcheck="true">/**
     * The console command description.
     *
     * @var string
     */</span>
    <span class="token keyword">protected</span> <span class="token variable">$description</span> <span class="token operator">=</span> <span class="token string">'Subscribe to a Redis channel'</span><span class="token punctuation">;</span>

    <span class="token comment" spellcheck="true">/**
     * Execute the console command.
     *
     * @return mixed
     */</span>
    <span class="token keyword">public</span> <span class="token keyword">function</span> <span class="token function">handle<span class="token punctuation">(</span></span><span class="token punctuation">)</span>
    <span class="token punctuation">{</span>
        <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">subscribe<span class="token punctuation">(</span></span><span class="token punctuation">[</span><span class="token string">'test-channel'</span><span class="token punctuation">]</span><span class="token punctuation">,</span> <span class="token keyword">function</span><span class="token punctuation">(</span><span class="token variable">$message</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
            <span class="token keyword">echo</span> <span class="token variable">$message</span><span class="token punctuation">;</span>
        <span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
    <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
    <p>Now we may publish messages to the channel using the <code class=" language-php">publish</code> method:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token scope">Route<span class="token punctuation">::</span></span><span class="token function">get<span class="token punctuation">(</span></span><span class="token string">'publish'</span><span class="token punctuation">,</span> <span class="token keyword">function</span> <span class="token punctuation">(</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
   <span class="token comment" spellcheck="true"> // Route logic...
</span>
    <span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">publish<span class="token punctuation">(</span></span><span class="token string">'test-channel'</span><span class="token punctuation">,</span> <span class="token function">json_encode<span class="token punctuation">(</span></span><span class="token punctuation">[</span><span class="token string">'foo'</span> <span class="token operator">=</span><span class="token operator">&gt;</span> <span class="token string">'bar'</span><span class="token punctuation">]</span><span class="token punctuation">)</span><span class="token punctuation">)</span><span class="token punctuation">;</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
    <h4>Wildcard Subscriptions</h4>
    <p>Using the <code class=" language-php">psubscribe</code> method, you may subscribe to a wildcard channel, which may be useful for catching all messages on all channels. The <code class=" language-php"><span class="token variable">$channel</span></code> name will be passed as the second argument to the provided callback <code class=" language-php">Closure</code>:</p>
    <pre class=" language-php"><code class=" language-php"><span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">psubscribe<span class="token punctuation">(</span></span><span class="token punctuation">[</span><span class="token string">'*'</span><span class="token punctuation">]</span><span class="token punctuation">,</span> <span class="token keyword">function</span><span class="token punctuation">(</span><span class="token variable">$message</span><span class="token punctuation">,</span> <span class="token variable">$channel</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
    <span class="token keyword">echo</span> <span class="token variable">$message</span><span class="token punctuation">;</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span>

<span class="token scope">Redis<span class="token punctuation">::</span></span><span class="token function">psubscribe<span class="token punctuation">(</span></span><span class="token punctuation">[</span><span class="token string">'users.*'</span><span class="token punctuation">]</span><span class="token punctuation">,</span> <span class="token keyword">function</span><span class="token punctuation">(</span><span class="token variable">$message</span><span class="token punctuation">,</span> <span class="token variable">$channel</span><span class="token punctuation">)</span> <span class="token punctuation">{</span>
    <span class="token keyword">echo</span> <span class="token variable">$message</span><span class="token punctuation">;</span>
<span class="token punctuation">}</span><span class="token punctuation">)</span><span class="token punctuation">;</span></code></pre>
<div>Nguồn: <a href="https://laravel.com/docs/5.3/redis">https://laravel.com/docs/5.3/redis</a></div>
</article>
@endsection