<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7bfd92f8f573bb55c1b3bfc01d8f66ed
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'ZionBuilderPro\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ZionBuilderPro\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'ZionBuilderPro\\Admin' => __DIR__ . '/../..' . '/includes/Admin.php',
        'ZionBuilderPro\\Api\\RestApi' => __DIR__ . '/../..' . '/includes/Api/RestApi.php',
        'ZionBuilderPro\\Api\\RestApiController' => __DIR__ . '/../..' . '/includes/Api/RestApiController.php',
        'ZionBuilderPro\\Api\\RestControllers\\AdobeFonts' => __DIR__ . '/../..' . '/includes/Api/RestControllers/AdobeFonts.php',
        'ZionBuilderPro\\Api\\RestControllers\\Icons' => __DIR__ . '/../..' . '/includes/Api/RestControllers/Icons.php',
        'ZionBuilderPro\\Api\\RestControllers\\MegaMenu' => __DIR__ . '/../..' . '/includes/Api/RestControllers/MegaMenu.php',
        'ZionBuilderPro\\Api\\RestControllers\\WPPageSelector' => __DIR__ . '/../..' . '/includes/Api/RestControllers/WPPageSelector.php',
        'ZionBuilderPro\\Api\\RestControllers\\WPTerms' => __DIR__ . '/../..' . '/includes/Api/RestControllers/WPTerms.php',
        'ZionBuilderPro\\Api\\RestControllers\\ZionApi' => __DIR__ . '/../..' . '/includes/Api/RestControllers/ZionApi.php',
        'ZionBuilderPro\\Assets' => __DIR__ . '/../..' . '/includes/Assets.php',
        'ZionBuilderPro\\Conditions\\Conditions' => __DIR__ . '/../..' . '/includes/Conditions/Conditions.php',
        'ZionBuilderPro\\Conditions\\PageRequest' => __DIR__ . '/../..' . '/includes/Conditions/PageRequest.php',
        'ZionBuilderPro\\Conditions\\RestController' => __DIR__ . '/../..' . '/includes/Conditions/RestController.php',
        'ZionBuilderPro\\Conditions\\Validations' => __DIR__ . '/../..' . '/includes/Conditions/Validations.php',
        'ZionBuilderPro\\DynamicContent\\BaseField' => __DIR__ . '/../..' . '/includes/DynamicContent/BaseField.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\AuthorInfo' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/AuthorInfo.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\AuthorMeta' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/AuthorMeta.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\CommentsNumber' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/CommentsNumber.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\CurrentUserInfo' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/CurrentUserInfo.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\CurrentUserMeta' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/CurrentUserMeta.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\FeaturedImage' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/FeaturedImage.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\FunctionReturnValue' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/FunctionReturnValue.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\GlobalColor' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/GlobalColor.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\GlobalGradient' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/GlobalGradient.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\LinkAuthorPage' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/LinkAuthorPage.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\LinkHomePage' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/LinkHomePage.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\LinkPostLink' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/LinkPostLink.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\MediaAuthorProfile' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/MediaAuthorProfile.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\MediaFeaturedImage' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/MediaFeaturedImage.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\MediaSiteLogo' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/MediaSiteLogo.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostContent' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostContent.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostCustomField' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostCustomField.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostDate' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostDate.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostExcerpt' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostExcerpt.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostId' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostId.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostTerms' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostTerms.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\PostTitle' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/PostTitle.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\RepeaterField' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/RepeaterField.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\Shortcode' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/Shortcode.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\SiteEmailAddress' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/SiteEmailAddress.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\SiteTagline' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/SiteTagline.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\SiteTimezone' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/SiteTimezone.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\SiteTitle' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/SiteTitle.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\TaxonomyDescription' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/TaxonomyDescription.php',
        'ZionBuilderPro\\DynamicContent\\Fields\\TaxonomyTitle' => __DIR__ . '/../..' . '/includes/DynamicContent/Fields/TaxonomyTitle.php',
        'ZionBuilderPro\\DynamicContent\\Manager' => __DIR__ . '/../..' . '/includes/DynamicContent/Manager.php',
        'ZionBuilderPro\\Editor' => __DIR__ . '/../..' . '/includes/Editor.php',
        'ZionBuilderPro\\ElementConditions\\ConditionsBase' => __DIR__ . '/../..' . '/includes/ElementConditions/ConditionsBase.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\AdvancedConditionals' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/AdvancedConditionals.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\Archive' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/Archive.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\Author' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/Author.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\Post' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/Post.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\User' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/User.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\WooCommerceConditionals' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/WooCommerceConditionals.php',
        'ZionBuilderPro\\ElementConditions\\Conditions\\WordPressConditionals' => __DIR__ . '/../..' . '/includes/ElementConditions/Conditions/WordPressConditionals.php',
        'ZionBuilderPro\\ElementConditions\\ElementConditions' => __DIR__ . '/../..' . '/includes/ElementConditions/ElementConditions.php',
        'ZionBuilderPro\\ElementConditions\\Rest' => __DIR__ . '/../..' . '/includes/ElementConditions/Rest.php',
        'ZionBuilderPro\\Elements' => __DIR__ . '/../..' . '/includes/Elements.php',
        'ZionBuilderPro\\Elements\\Accordions\\AccordionItem' => __DIR__ . '/../..' . '/includes/Elements/Accordions/AccordionItem.php',
        'ZionBuilderPro\\Elements\\Countdown\\Countdown' => __DIR__ . '/../..' . '/includes/Elements/Countdown/Countdown.php',
        'ZionBuilderPro\\Elements\\CustomCode\\CustomCode' => __DIR__ . '/../..' . '/includes/Elements/CustomCode/CustomCode.php',
        'ZionBuilderPro\\Elements\\HeaderBuilder\\HeaderBuilder' => __DIR__ . '/../..' . '/includes/Elements/HeaderBuilder/HeaderBuilder.php',
        'ZionBuilderPro\\Elements\\InnerContent\\InnerContent' => __DIR__ . '/../..' . '/includes/Elements/InnerContent/InnerContent.php',
        'ZionBuilderPro\\Elements\\Menu\\Menu' => __DIR__ . '/../..' . '/includes/Elements/Menu/Menu.php',
        'ZionBuilderPro\\Elements\\Menu\\ZionMenuWalker' => __DIR__ . '/../..' . '/includes/Elements/Menu/ZionMenuWalker.php',
        'ZionBuilderPro\\Elements\\Modal\\Modal' => __DIR__ . '/../..' . '/includes/Elements/Modal/Modal.php',
        'ZionBuilderPro\\Elements\\Pagination\\Pagination' => __DIR__ . '/../..' . '/includes/Elements/Pagination/Pagination.php',
        'ZionBuilderPro\\Elements\\PostComments\\PostComments' => __DIR__ . '/../..' . '/includes/Elements/PostComments/PostComments.php',
        'ZionBuilderPro\\Elements\\Rating\\Rating' => __DIR__ . '/../..' . '/includes/Elements/Rating/Rating.php',
        'ZionBuilderPro\\Elements\\Search\\Search' => __DIR__ . '/../..' . '/includes/Elements/Search/Search.php',
        'ZionBuilderPro\\Elements\\SliderBuilder\\SliderBuilder' => __DIR__ . '/../..' . '/includes/Elements/SliderBuilder/SliderBuilder.php',
        'ZionBuilderPro\\Elements\\SliderBuilder\\SliderBuilderSlide' => __DIR__ . '/../..' . '/includes/Elements/SliderBuilder/SliderBuilderSlide.php',
        'ZionBuilderPro\\Elements\\SocialShare\\SocialShare' => __DIR__ . '/../..' . '/includes/Elements/SocialShare/SocialShare.php',
        'ZionBuilderPro\\Elements\\Tabs\\Tabs' => __DIR__ . '/../..' . '/includes/Elements/Tabs/Tabs.php',
        'ZionBuilderPro\\Elements\\Tabs\\TabsItem' => __DIR__ . '/../..' . '/includes/Elements/Tabs/TabsItem.php',
        'ZionBuilderPro\\Environment' => __DIR__ . '/../..' . '/includes/Environment.php',
        'ZionBuilderPro\\Features\\AdditionalPageOptions' => __DIR__ . '/../..' . '/includes/Features/AdditionalPageOptions.php',
        'ZionBuilderPro\\Features\\Connector\\Api\\Api' => __DIR__ . '/../..' . '/includes/Features/Connector/Api/Api.php',
        'ZionBuilderPro\\Features\\Connector\\Connector' => __DIR__ . '/../..' . '/includes/Features/Connector/Connector.php',
        'ZionBuilderPro\\Features\\Connector\\OptionsSchema' => __DIR__ . '/../..' . '/includes/Features/Connector/OptionsSchema.php',
        'ZionBuilderPro\\Features\\Connector\\Sources\\ExternalSource' => __DIR__ . '/../..' . '/includes/Features/Connector/Sources/ExternalSource.php',
        'ZionBuilderPro\\Features\\CustomCSS' => __DIR__ . '/../..' . '/includes/Features/CustomCSS.php',
        'ZionBuilderPro\\Fonts\\Fonts' => __DIR__ . '/../..' . '/includes/Fonts/Fonts.php',
        'ZionBuilderPro\\Fonts\\Providers\\AdobeFontsProvider' => __DIR__ . '/../..' . '/includes/Fonts/Providers/AdobeFontsProvider.php',
        'ZionBuilderPro\\Fonts\\Providers\\CustomFonts' => __DIR__ . '/../..' . '/includes/Fonts/Providers/CustomFonts.php',
        'ZionBuilderPro\\Fonts\\Providers\\TypeKit' => __DIR__ . '/../..' . '/includes/Fonts/Providers/TypeKit.php',
        'ZionBuilderPro\\Frontend' => __DIR__ . '/../..' . '/includes/Frontend.php',
        'ZionBuilderPro\\Icons' => __DIR__ . '/../..' . '/includes/Icons.php',
        'ZionBuilderPro\\Integrations' => __DIR__ . '/../..' . '/includes/Integrations.php',
        'ZionBuilderPro\\Integrations\\ACF\\Acf' => __DIR__ . '/../..' . '/includes/Integrations/ACF/Acf.php',
        'ZionBuilderPro\\Integrations\\ACF\\AcfRepeaterProvider' => __DIR__ . '/../..' . '/includes/Integrations/ACF/AcfRepeaterProvider.php',
        'ZionBuilderPro\\Integrations\\ACF\\Fields\\AcfFieldBase' => __DIR__ . '/../..' . '/includes/Integrations/ACF/Fields/AcfFieldBase.php',
        'ZionBuilderPro\\Integrations\\ACF\\Fields\\AcfFieldTypeImage' => __DIR__ . '/../..' . '/includes/Integrations/ACF/Fields/AcfFieldTypeImage.php',
        'ZionBuilderPro\\Integrations\\ACF\\Fields\\AcfFieldTypeLink' => __DIR__ . '/../..' . '/includes/Integrations/ACF/Fields/AcfFieldTypeLink.php',
        'ZionBuilderPro\\Integrations\\ACF\\Fields\\AcfFieldTypeText' => __DIR__ . '/../..' . '/includes/Integrations/ACF/Fields/AcfFieldTypeText.php',
        'ZionBuilderPro\\Integrations\\Metabox\\Fields\\Image' => __DIR__ . '/../..' . '/includes/Integrations/Metabox/Fields/Image.php',
        'ZionBuilderPro\\Integrations\\Metabox\\Fields\\Link' => __DIR__ . '/../..' . '/includes/Integrations/Metabox/Fields/Link.php',
        'ZionBuilderPro\\Integrations\\Metabox\\Fields\\Text' => __DIR__ . '/../..' . '/includes/Integrations/Metabox/Fields/Text.php',
        'ZionBuilderPro\\Integrations\\Metabox\\Metabox' => __DIR__ . '/../..' . '/includes/Integrations/Metabox/Metabox.php',
        'ZionBuilderPro\\Integrations\\Metabox\\Traits\\Base' => __DIR__ . '/../..' . '/includes/Integrations/Metabox/Traits/Base.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ArchiveAddToCart\\ArchiveAddToCart' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ArchiveAddToCart/ArchiveAddToCart.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CartCrossSells\\CartCrossSells' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CartCrossSells/CartCrossSells.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CartProducts\\CartProducts' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CartProducts/CartProducts.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CartTotals\\CartTotals' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CartTotals/CartTotals.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutCoupon\\CheckoutCoupon' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutCoupon/CheckoutCoupon.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutCustomerDetails\\CheckoutCustomerDetails' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutCustomerDetails/CheckoutCustomerDetails.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutFormWrapper\\CheckoutFormWrapper' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutFormWrapper/CheckoutFormWrapper.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutLogin\\CheckoutLogin' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutLogin/CheckoutLogin.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutOrderReview\\CheckoutOrderReview' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutOrderReview/CheckoutOrderReview.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\CheckoutThankYou\\CheckoutThankYou' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/CheckoutThankYou/CheckoutThankYou.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\MiniCart\\MiniCart' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/MiniCart/MiniCart.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\Notices\\Notices' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/Notices/Notices.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductAddToCart\\ProductAddToCart' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductAddToCart/ProductAddToCart.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductAdditionalInfo\\ProductAdditionalInfo' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductAdditionalInfo/ProductAdditionalInfo.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductBreadcrumbs\\ProductBreadcrumbs' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductBreadcrumbs/ProductBreadcrumbs.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductDescription\\ProductDescription' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductDescription/ProductDescription.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductImages\\ProductImages' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductImages/ProductImages.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductMeta\\ProductMeta' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductMeta/ProductMeta.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductPrice\\ProductPrice' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductPrice/ProductPrice.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductRating\\ProductRating' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductRating/ProductRating.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductRelated\\ProductRelated' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductRelated/ProductRelated.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductReviewsForm\\ProductReviewsForm' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductReviewsForm/ProductReviewsForm.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductStock\\ProductStock' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductStock/ProductStock.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductTabs\\ProductTabs' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductTabs/ProductTabs.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductTitle\\ProductTitle' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductTitle/ProductTitle.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\ProductUpSells\\ProductUpSells' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/ProductUpSells/ProductUpSells.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\WooBilling\\WooBilling' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/WooBilling/WooBilling.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\WooCheckoutInfo\\WooCheckoutInfo' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/WooCheckoutInfo/WooCheckoutInfo.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\WooCheckoutPayment\\WooCheckoutPayment' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/WooCheckoutPayment/WooCheckoutPayment.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\Elements\\WooCommerceElement' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/Elements/WooCommerceElement.php',
        'ZionBuilderPro\\Integrations\\WooCommerce\\WooCommerce' => __DIR__ . '/../..' . '/includes/Integrations/WooCommerce/WooCommerce.php',
        'ZionBuilderPro\\License' => __DIR__ . '/../..' . '/includes/License.php',
        'ZionBuilderPro\\MegaMenu' => __DIR__ . '/../..' . '/includes/MegaMenu.php',
        'ZionBuilderPro\\Permissions' => __DIR__ . '/../..' . '/includes/Permissions.php',
        'ZionBuilderPro\\Plugin' => __DIR__ . '/../..' . '/includes/Plugin.php',
        'ZionBuilderPro\\ProMasks' => __DIR__ . '/../..' . '/includes/ProMasks.php',
        'ZionBuilderPro\\Repeater' => __DIR__ . '/../..' . '/includes/Repeater.php',
        'ZionBuilderPro\\Repeater\\Providers\\ActivePageQuery' => __DIR__ . '/../..' . '/includes/Repeater/Providers/ActivePageQuery.php',
        'ZionBuilderPro\\Repeater\\Providers\\QueryBuilder' => __DIR__ . '/../..' . '/includes/Repeater/Providers/QueryBuilder.php',
        'ZionBuilderPro\\Repeater\\Providers\\RecentPosts' => __DIR__ . '/../..' . '/includes/Repeater/Providers/RecentPosts.php',
        'ZionBuilderPro\\Repeater\\RepeaterElement' => __DIR__ . '/../..' . '/includes/Repeater/RepeaterElement.php',
        'ZionBuilderPro\\Repeater\\RepeaterProvider' => __DIR__ . '/../..' . '/includes/Repeater/RepeaterProvider.php',
        'ZionBuilderPro\\Requirements' => __DIR__ . '/../..' . '/includes/Requirements.php',
        'ZionBuilderPro\\Scripts' => __DIR__ . '/../..' . '/includes/Scripts.php',
        'ZionBuilderPro\\ThemeBuilder\\RestController' => __DIR__ . '/../..' . '/includes/ThemeBuilder/RestController.php',
        'ZionBuilderPro\\ThemeBuilder\\ThemeBuilder' => __DIR__ . '/../..' . '/includes/ThemeBuilder/ThemeBuilder.php',
        'ZionBuilderPro\\ThemeBuilder\\WP_Request' => __DIR__ . '/../..' . '/includes/ThemeBuilder/WP_Request.php',
        'ZionBuilderPro\\Utils' => __DIR__ . '/../..' . '/includes/Utils.php',
        'ZionBuilderPro\\WhiteLabel' => __DIR__ . '/../..' . '/includes/WhiteLabel.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7bfd92f8f573bb55c1b3bfc01d8f66ed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7bfd92f8f573bb55c1b3bfc01d8f66ed::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7bfd92f8f573bb55c1b3bfc01d8f66ed::$classMap;

        }, null, ClassLoader::class);
    }
}