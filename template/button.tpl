{strip}
{footer_script require='customMugJs'}
{literal}
document.addEventListener('DOMContentLoaded', function () {
  MugShot.init(
    {/literal}{$MUGSHOTS}{literal},
    {/literal}{$IMAGE_ID}{literal},
    {/literal}'{$MUGSHOT_ACTION}'{literal});
});

{/literal}{/footer_script}

{if MUGSHOT_USER_ADMIN}
  {if BOOT == 1}
    <li class="nav-item">
      <a id="mugshot" class="nav-link" href="#" onclick="MugShot.frame()" title="{'Name that face!'|translate}" rel="nofollow">
          <i class="mugshot-icon mugshot-icon-capone" aria-hidden="true"></i><span class="d-lg-none ml-2">Tag people you know</span>
      </a>
    </li>
  {elseif BOOT == 0}
    <a id="mugshot" class="pwg-state-default" href="#" onclick="MugShot.frame()" title="{'Name that face!'|translate}" rel="nofollow">
        <span style="font-size:22px;" class="mugshot-icon mugshot-icon-capone" aria-hidden="true"></span>
        <span class="pwg-button-text">{'Name that mug!'|translate}</span>
    </a>
  {/if}
{/if}
{/strip}
