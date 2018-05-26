{if isset($extraimages) && !empty($extraimages)}
    <ul class="extra-images clearfix">
        {if $manufacturer}
        <li class="extra-images__item">
            <img src="{$base_uri}/img/m/{$manufacturer->id}.jpg"/>
        </li>
        {/if}
        {foreach from=$extraimages item=image}
            <li class="extra-images__item">
                <img src="{$base_uri}/{$image.src}" />
            </li>
        {/foreach}
    </ul>
{/if}