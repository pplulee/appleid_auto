<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shadowrocket 使用教程 &mdash; {$config["appName"]}</title>
    <link href="{$metron['assets_url']}/css/client/metron-icon.css" rel="stylesheet" type="text/css" />
    <link href="{$metron['assets_url']}/plugins/tutorial/lightbox/lightbox.min.css" rel="stylesheet" >
    {include file='include/global/head.tpl'}
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-row flex-column-fluid page">
            <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
                {include file='include/global/menu.tpl'}
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <div class="subheader min-h-lg-175px pt-5 pb-7 subheader-transparent" id="kt_subheader">
                        <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
                            <div class="d-flex align-items-center flex-wrap mr-2">
                                <div class="d-flex flex-column">
                                    <h2 class="text-white font-weight-bold my-2 mr-5">Shadowrocket 使用教程</h2>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column-fluid">
                        <div class="container">

                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-custom gutter-b {$metron['style_shadow']}">
                                        <div class="card-header">
                                            <div class="card-title">
                                            </div>
                                        </div>
                                        <div class="card-body">

                                            <div class="row p-5">
                                                <div class="col-sm-12 col-md-12 col-lg-7 pb-5">
                                                    <p class="font-size-h1 pb-5"><strong>0. 简介</strong></p>
                                                    <div class="example-preview">
                                                        <p>Shadowrocket是一款基于规则的网络调试工具，具有性能高和稳定好、使用流畅，功能多样的特点，只占用最少的系统资源，全盘接管所有的连接，根据规则来进行处理，让你无忧无虑。独有的场景模式，方便根据不同需求自动切换配置及节点。</p>
                                                        <p>由于App Store中国区禁止上架VPN类所有软件，所以该软件仅在美区、港区等非国区App Store可下载，并且该软件是收费软件，为开发者收取。
                                                            {if $metron['ios_account'] != ''}
                                                        <p>本站对符合要求的会员提供已购买该软件的Apple ID共享账号进行下载，有条件建议自行搜索相关教程注册非国区账号购买该软件以支持开发者。</p>
                                                        {/if}</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-5">
                                                    <a class="image-popup-no-margins" href="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/shadowrocket.jpg" data-lightbox="images">
                                                        <img class="rounded-lg" style="width:100%" src="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/shadowrocket.jpg"/></a>
                                                </div>
                                            </div>

                                            <div class="separator separator-dashed separator-border-4"></div>

                                            <div class="row p-5">
                                                <div class="col-sm-12 col-md-12 col-lg-7 pb-5">
                                                    <p class="font-size-h1 pb-5"><strong>1. 通过 App Store 安装客户端</strong></p>
                                                        {if $user->class >= $metron['ios_class']}
                                                            {if empty($apple_accounts)}
                                                                <p class="mb-2">账号获取失败，请联系管理员。</p>
                                                            {else}
                                                                {foreach $apple_accounts as $account}
                                                                <div class="row">
                                                                    <div class="input-group mb-3 col-md-8 col-lg-8">
                                                                        <input type="text" class="form-control" value="{$account['username']}" disabled="disabled">
                                                                        <div class="input-group-append">
                                                                            <button class="btn btn-primary copy-text" type="button" data-clipboard-text="{$account['username']}">复制账号</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="input-group mb-3 col-md-8 col-lg-8">
                                                                        <input type="text" class="form-control" value="{$account['password']}" disabled="disabled">
                                                                        <div class="input-group-append">
                                                                            <button class="btn btn-primary copy-text" type="button" data-clipboard-text="{$account['password']}">复制密码</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="input-group mb-3 col-md-8 col-lg-8">
                                                                        <input type="text" class="form-control" value="上次检查：{$account['last_check']} 账号状态：{($account['status'])?"正常":"异常"}" disabled="disabled">
                                                                    </div>
                                                                </div>
                                                                {/foreach}
                                                                <p class="mb-2">如果账号被锁定，可联系管理员等待解锁，也可自行从其他渠道获取</p>
                                                            {/if}
                                                        {else}
                                                            <div class="alert alert-primary" role="alert">
                                                                <div class="alert-text mt-3">
                                                                    <h4 class="alert-heading">您的等级不支持查看共享账号</h4>
                                                                    <hr>
                                                                    <p class="mb-2">Shadowrocket为App Store商店付费软件，为防止频繁登录而导致被封号，共享账号仅对 <code>Lv.{$metron['ios_class']}及以上会员</code> 提供。<br>
                                                                        当您成为 <code>Lv.{$metron['ios_class']}及以上会员</code> 时(不包括试用会员)，该页面会自动显示共享账号。<br>
                                                                        您可通过其他渠道进行安装下载Shadowrocket，具体可至搜索引擎搜索资源。</p>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                    <br />
                                                    <p class="mb-2">打开手机<code>设置</code> -> <code>iTunes Store 与 App Store</code> ->使用 <code>非国区 Apple ID </code>登录</p>
                                                    <p class="mb-2">登录成功后 App Store 会切换对应的语言，在搜索框输入 <code>Shadowrocket</code> 进行安装</p>
                                                    <p class="mb-2">安装完成可正常打开app后 打开手机<code>设置</code> -> <code>iTunes Store 与 App Store</code> 退出共享账号，安装完成后不再需要Appe ID，所以请不要保留在手机登录。</p>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-5">
                                                    <a class="image-popup-no-margins" href="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/01.png" data-lightbox="images">
                                                        <img class="rounded-lg" style="width:100%" src="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/01.png"/></a>
                                                </div>
                                            </div>

                                            <div class="separator separator-dashed separator-border-4"></div>

                                            <div class="row p-5">
                                                <div class="col-sm-12 col-md-12 col-lg-7 pb-5">
                                                    <p class="font-size-h1 pb-5"><strong>2. 设置订阅地址</strong></p>
                                                    <div class="btn-group mb-3 mr-3">
                                                        <button type="button" class="btn btn-pill btn-shadowrocket dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">&nbsp;&nbsp;<i class="metron-shadowrocket text-white"></i>Shadowrocket 订阅&nbsp;&nbsp;</button>
                                                        <div class="dropdown-menu">
                                                            <button type="button" class="dropdown-item copy-text" data-clipboard-text="{$subInfo["shadowrocket"]}">复制 Shadowrocket 订阅</button>
                                                            <div class="dropdown-divider"></div>
                                                            <button type="button" class="dropdown-item" href="##" onclick="importSublink('shadowrocket')">一键导入 Shadowrocket</button>
                                                        </div>
                                                    </div>
                                                    <p class="mb-2">点击上方按钮一键导入节点配置到Shadowrocket</p>
                                                    <p class="mb-2">如果导入后没有显示节点，请重启Shadowrocket</p>
                                                    <p class="mb-2">点击底栏的 <code>设置</code> -> <code>服务器订阅</code> -> 启用 <code>打开时更新</code></p>
                                                    <p class="mb-2">这样以后每次打开客户端都会自动更新最新节点</p>
                                                    <br />

                                                    <div class="alert alert-custom alert-outline-primary fade show mb-5" role="alert">
                                                        <div class="alert-icon">
                                                            <i class="flaticon-warning"></i>
                                                        </div>
                                                        <div class="alert-text">
                                                            <p class="mb-2">无法一键导入? 试试手动设置订阅</p>
                                                            <p class="mb-2">在APP首页点击右上角 + 号</p>
                                                            <p class="mb-2">类型 选择 <code>Subscribe</code> , 并粘贴订阅地址到URL一栏, 备注填写 {$config["appName"]} - 点击完成</p>
                                                        </div>
                                                        <div class="alert-close">
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="关">
                                                                    <span aria-hidden="true">
                                                                        <i class="ki ki-close"></i>
                                                                    </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-5">
                                                    <a class="image-popup-no-margins" href="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/02.png" data-lightbox="images">
                                                        <img class="rounded-lg" style="width:100%" src="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/02.png"/></a>
                                                </div>
                                            </div>

                                            <div class="separator separator-dashed separator-border-4"></div>

                                            <div class="row p-5">
                                                <div class="col-sm-12 col-md-12 col-lg-7 pb-5">
                                                    <p class="font-size-h1 pb-5"><strong>3. 开启代理</strong></p>
                                                    <p class="mb-2"><code>回到首页</code>，点击进入<code>全局路由</code>将其更改为 <code>配置</code></p>
                                                    <p class="mb-2">在节点列表中选择节点，小黄点表示当前选择的节点</p>
                                                    <p class="mb-2">点击最顶上的开关开启代理</p>
                                                    <p class="mb-2">如果是首次开启会弹出VPN文件的应用，务必点击<code>Allow</code>并验证</p>
                                                </div>
                                                <div class="col-sm-12 col-md-12 col-lg-5">
                                                    <a class="image-popup-no-margins" href="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/04.png" data-lightbox="images">
                                                        <img class="rounded-lg" style="width:100%" src="{$metron['assets_url']}/media/tutorial/ios/shadowrocket/04.png"/></a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                {include file='include/global/footer.tpl'}
            </div>
        </div>
    </div>

    {include file='include/global/scripts.tpl'}
    {include file='include/global/import_sublink.tpl'}
    <script src="{$metron['assets_url']}/plugins/tutorial/lightbox/lightbox.min.js" type="text/javascript"></script>
    </body>

</html>
