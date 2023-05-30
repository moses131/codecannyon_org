<?php

namespace theme\helpers;

class parts {

    public static function main_logo() {
        $logo = get_theme_option( 'logo_normal' );
        return '
        <div class="logo">
            <img src="' . ( !empty( $logo ) ? current( $logo ) : theme_url( 'assets/img/logo.png' ) ) . '" alt="">
        </div>';
    }

    public static function second_logo() {
        $logo = get_theme_option( 'logo_small' );
        return '
        <div class="logo logo2">
            <img src="' . ( !empty( $logo ) ? current( $logo ) : theme_url( 'assets/img/logo2.png' ) ) . '" alt="">
        </div>';
    }

    public static function index() {
        $options = theme_option_lang( 'index' );
        if( empty( $options['sections'] ) )
        return self::default_index();
        
        $sections_parts = [];
        $i = 0;
        foreach( $options['sections'] as $section ) {
            $content = '
            <div class="twocol defp ' . ( $i % 2 == 0 ? 'bg1' : 'imf' ) . '">
                <div class="main-wrapper">
                    <h2 class="title">' . esc_html( $section['title'] ) . '</h2>
                    <div class="cols">';
                        if( !empty( $section['image'] ) ) {
                            $image = current( $section['image'] );
                            $content .= '
                            <div class="img">
                                <img src="' . esc_html( $image ) . '" alt="" />
                            </div>';
                        }
                        $content .= '
                        <div class="txt">' . esc_html( $section['text'] ) . '</div>
                    </div>
                </div>
            </div>';
            $sections_parts[] = $content;
            $i++;
        }

        return implode( "\n", $sections_parts );
    }

    public static function index_boxes() {
        $options = theme_option_lang( 'index' );
        if( empty( $options['boxes'] ) )
        return self::default_index_boxes();
        
        $content = '
        <div class="bg1 defp">
        <div class="main-wrapper">
        <ul class="boxes">';

        foreach( $options['boxes'] as $section ) {
            $content .= '
            <li>
                <h2>' . esc_html( $section['title'] ) . '</h2>
                <div>' . esc_html( $section['text'] ) . '</div>';
                if( !empty( $section['link_name'] ) )
                $content .= '<a href="' . ( !empty( $section['link_url'] ) ? esc_url( $section['link_url'] ) : '#' ) . '" class="btn">' . esc_html( $section['link_name'] ) . '</a>';
                $content .= '
            </li>';
        }

        $content .= '
        </ul>
        </div>
        </div>';

        return $content;
    }

    public static function index_cta() {
        $options    = theme_option_lang( 'index' );
        $cta        = $options['cta'] ?? NULL;

        if( empty( $cta['text'] ) )
        return;

        $content = '
        <div class="defp">
            <div class="main-wrapper">
                <div class="cta-box">
                    ' . esc_html( $cta['text'] );
                    if( !empty( $cta['link_name'] ) ) {
                        $content .= '
                        <div class="mt30">
                            <a href="' . ( !empty( $cta['link_url'] ) ? esc_url( $cta['link_url'] ) : '#' ) . '" class="btn s2 big">' . esc_html( $cta['link_name']) . '</a>
                        </div>';
                    }
                $content .= '
                </div>
            </div>
        </div>';

        return $content;
    }

    public static function respondents() {
        $options = theme_option_lang( 'respondents' );
        if( empty( $options['sections'] ) )
        return self::default_respondents();
        
        $sections_parts = [];
        $i = 0;
        foreach( $options['sections'] as $section ) {
            $content = '
            <div class="twocol defp ' . ( $i % 2 == 1 ? '' : 'bg1 imf' ) . '">
                <div class="main-wrapper">
                    <h2 class="title">' . esc_html( $section['title'] ) . '</h2>
                    <div class="cols">';
                        if( !empty( $section['image'] ) ) {
                            $image = current( $section['image'] );
                            $content .= '
                            <div class="img">
                                <img src="' . esc_html( $image ) . '" alt="" />
                            </div>';
                        }
                        $content .= '
                        <div class="txt">' . esc_html( $section['text'] ) . '</div>
                    </div>
                </div>
            </div>';
            $sections_parts[] = $content;
            $i++;
        }

        return implode( "\n", $sections_parts );
    }

    public static function respondents_cta() {
        $options    = theme_option_lang( 'respondents' );
        $cta        = $options['cta'] ?? NULL;

        if( empty( $cta['text'] ) )
        return;

        $content = '
        <div class="defp">
            <div class="main-wrapper">
                <div class="cta-box">
                    ' . esc_html( $cta['text'] );
                    if( !empty( $cta['link_name'] ) ) {
                        $content .= '
                        <div class="mt30">
                            <a href="' . ( !empty( $cta['link_url'] ) ? esc_url( $cta['link_url'] ) : '#' ) . '" class="btn s2 big">' . esc_html( $cta['link_name']) . '</a>
                        </div>';
                    }
                $content .= '
                </div>
            </div>
        </div>';

        return $content;
    }

    public static function pricing_personal() {
        $options = theme_option_lang( 'pricing' );
        if( empty( $options['personal'] ) )
        return self::default_pricing_personal();
        
        $sections_parts = [];
        $i = 0;
        foreach( $options['personal'] as $section ) {
            $content = '
            <div class="twocol defp ' . ( $i % 2 == 1 ? '' : 'bg1 imf' ) . '">
                <div class="main-wrapper">
                    <h2 class="title">' . esc_html( $section['title'] ) . '</h2>
                    <div class="cols">';
                        if( !empty( $section['image'] ) ) {
                            $image = current( $section['image'] );
                            $content .= '
                            <div class="img">
                                <img src="' . esc_html( $image ) . '" alt="" />
                            </div>';
                        }
                        $content .= '
                        <div class="txt">' . esc_html( $section['text'] ) . '</div>
                    </div>
                </div>
            </div>';
            $sections_parts[] = $content;
            $i++;
        }

        return implode( "\n", $sections_parts );
    }

    public static function pricing_team() {
        $options = theme_option_lang( 'pricing' );
        if( empty( $options['team'] ) )
        return self::default_pricing_team();
        
        $sections_parts = [];
        $i = 0;
        foreach( $options['team'] as $section ) {
            $content = '
            <div class="twocol defp ' . ( $i % 2 == 0 ? '' : 'bg1 imf' ) . '">
                <div class="main-wrapper">
                    <h2 class="title">' . esc_html( $section['title'] ) . '</h2>
                    <div class="cols">';
                        if( !empty( $section['image'] ) ) {
                            $image = current( $section['image'] );
                            $content .= '
                            <div class="img">
                                <img src="' . esc_html( $image ) . '" alt="" />
                            </div>';
                        }
                        $content .= '
                        <div class="txt">' . esc_html( $section['text'] ) . '</div>
                    </div>
                </div>
            </div>';
            $sections_parts[] = $content;
            $i++;
        }

        return implode( "\n", $sections_parts );
    }

    public static function pricing_cta() {
        $options    = theme_option_lang( 'pricing' );
        $cta        = $options['cta'] ?? NULL;

        if( empty( $cta['text'] ) )
        return;

        $content = '
        <div class="defp">
            <div class="main-wrapper">
                <div class="cta-box">
                    ' . esc_html( $cta['text'] );
                    if( !empty( $cta['link_name'] ) ) {
                        $content .= '
                        <div class="mt30">
                            <a href="' . ( !empty( $cta['link_url'] ) ? esc_url( $cta['link_url'] ) : '#' ) . '" class="btn s2 big">' . esc_html( $cta['link_name']) . '</a>
                        </div>';
                    }
                $content .= '
                </div>
            </div>
        </div>';

        return $content;
    }

    public static function faq_cta() {
        $options    = theme_option_lang( 'faqs' );
        $cta        = $options['cta'] ?? NULL;

        if( empty( $cta['text'] ) )
        return;

        $content = '
        <div class="defp">
            <div class="main-wrapper">
                <div class="cta-box">
                    ' . esc_html( $cta['text'] );
                    if( !empty( $cta['link_name'] ) ) {
                        $content .= '
                        <div class="mt30">
                            <a href="' . ( !empty( $cta['link_url'] ) ? esc_url( $cta['link_url'] ) : '#' ) . '" class="btn s2 big">' . esc_html( $cta['link_name']) . '</a>
                        </div>';
                    }
                $content .= '
                </div>
            </div>
        </div>';

        return $content;
    }

    private static function default_index() {
        $sections   = [];
        $sections[] = '
        <div class="twocol defp bg1 imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Building beautiful surveys', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/1.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'You can choose from a vast variety of inputs in order to create even the most complex surveys. Or you can choose from one of our pre-made templates. Either way, your success is guaranteed, because if you care about the stats, there is no other option.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Collect responses in style', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/2.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'When you consider that your survey is ready, create your first collector and start collecting responses right away. Publish the link on your website, and social profiles, or send it via email. If this is not your style, collect responses from our members.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp bg1 imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Analize responses like a PRO', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/3.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Create reports, save and compare through time. Know the evolution of your product or service and act. Collect responses and learn what your customers need.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Work with your team', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/4.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Invite the whole team to join you and analyze everything together. All members of your team can create their own reports, and share them with other members of the team.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';

        return implode( "\n", $sections );
    }

    private static function default_respondents() {
        $sections   = [];
        $sections[] = '
        <div class="twocol bg1 defp imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Respond and make money', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/5.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Respond to surveys and make money, it is simple as that. You can also gain loyalty points and exchange them for nice gifts in the loyalty rewards shop.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'When you want, how you want', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/6.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Respond to surveys when you want on any device. Just visit our website, sign in and that is all. You choose the surveys you want to respond. Easy!', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';

        $sections[] = '
        <div class="twocol bg1 defp imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Invite friends, get loyalty points', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/7.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Our website is free. If your friends decide to upgrade their accounts, you get points for every month they purchase. You also get points when they register and verify their accounts.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';

        return implode( "\n", $sections );
    }

    private static function default_pricing_personal() {
        $sections   = [];
        $sections[] = '
        <div class="twocol bg1 defp imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Free, no credit card required', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/8.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Our services are absolutely free and you can start immediately without any credit card required. No trial version so you can upgrade when you want and only if you need.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Amazing tools, no restrictions', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/9.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'All question types are free to use. No restrictions. Join us today and start with a free account without any restrictions.  Collect responses free from your sources.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';

        return implode( "\n", $sections );
    }

    private static function default_pricing_team() {
        $sections   = [];
        $sections[] = '
        <div class="twocol defp imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'With the team is easier', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/10.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( "Your team, your account! All surveys are associated with your account, so you don't need to pay for every single team member. Your account is enough for all of your team!", 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol bg1 defp">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Keep in touch', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/11.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( 'Each member of your team can create their own reports. They can share the reports with one member of the team or with everyone. In-app chat with your team.', 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';
        $sections[] = '
        <div class="twocol defp imf">
            <div class="main-wrapper">
                <h2 class="title">' . t( 'Work offline?', 'def-theme' ) . '</h2>
                <div class="cols">
                    <div class="img">
                        <img src="' . theme_url( 'assets/img/12.svg' ) . '" alt="">
                    </div>
                    <div class="txt">
                    ' . t( "Of course you can use your online research to the office. Print a report, print a comparison between reports, and analyze with your team offline", 'def-theme' ) . '
                    </div>
                </div>
            </div>
        </div>';

        return implode( "\n", $sections );
    }

    private static function default_index_boxes() {
        return '
        <div class="bg4 defp">
            <div class="main-wrapper">
                <ul class="boxes">
                    <li>
                        <h2>' . t( 'Create amazing surveys', 'def-theme' ) . '</h2>
                        <div>' . t( 'Sign up today and start your first survey with absolutely no charges. No credit card required.', 'def-theme' ) . '</div>
                        <a href="' . admin_url() . '" class="btn">' . t( 'Join today', 'def-theme' ) . '</a>
                    </li>
                    <li>
                        <h2>' . t( 'Respond and make real money', 'def-theme' ) . '</h2>
                        <div>' . t( 'Respond to surveys and get commissions. Real money and/or loyalty points.', 'def-theme' ) . '</div>
                        <a href="' . admin_url() . '" class="btn">' . t( 'Join today', 'def-theme' ) . '</a>
                    </li>
                </ul>
            </div>
        </div>';
    }

}