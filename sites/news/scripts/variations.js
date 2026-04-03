const UCSC_MEDIA_COVERAGE_LOOP = 'ucsc-news-functionality/media-coverage-loop';
const UCSC_NEWS_ARTICLE_LOOP = 'ucsc-news-functionality/news-article-loop';

const { registerBlockVariation } = wp.blocks;

/**
 * Add core/query block variation limited to media coverage only 
 */
registerBlockVariation( 'core/query', {
    name: UCSC_MEDIA_COVERAGE_LOOP,
    title: 'Media Coverage Loop',
    description: 'This block displays Media Coverage items',
    isActive: ( { namespace, query } ) => {
        return (
            namespace === UCSC_MEDIA_COVERAGE_LOOP
            && query.postType === 'media_coverage'
        );
    },
    icon: 'editor-ul',
    allowedControls: [ 'inherit', 'order', 'taxQuery', 'search' ],
    attributes: {
        namespace: UCSC_MEDIA_COVERAGE_LOOP,
        query: {
            perPage: 9,
            pages: 0,
            offset: 0,
            postType: 'media_coverage',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: true,
        },
    },
    scope: [ 'inserter' ],
    innerBlocks: [
    ['core/post-template',{"layout":{"type":"default","columnCount":3}},
        [
            ['core/columns', {"isStackedOnMobile":false,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}},[
                ['core/column',{"width":"20%"},
                    [
                        [ 'core/post-featured-image' ]
                    ]    
                ],
                ['core/column',{"width":"80%"},
                    [
                        ['core/group', {"style":{"spacing":{"padding":{"top":"0px","right":"20px","bottom":"0px","left":"20px"},"blockGap":"1rem"}},"layout":{"inherit":false}},[
                            ['acf/article-source', {"name":"acf/article-source","mode":"preview","className":"ucsc-media-coverage-block__post-source","textColor":"dark-gray","style":{"elements":{"link":{"color":{"text":"var:preset|color|dark-gray"}}}}}],
                            [ 'core/post-title', {"isLink":true,"style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"0","bottom":"0"}}}} ],
                            [ 'core/post-date'],
                            [ 'core/post-excerpt' ]
                        ]]    
                    ]    
                ]
            ]]
        ],
    ],
    [ 'core/query-pagination' ],
    [ 'core/query-no-results' ],
],
    }
);


/**
 * Add core/query block variation limited to posts only 
 */
registerBlockVariation( 'core/query', {
    name: UCSC_NEWS_ARTICLE_LOOP,
    title: 'News Article Loop',
    description: 'Block to display news articles.',
    isActive: ( { namespace, query } ) => {
        return (
            namespace === UCSC_NEWS_ARTICLE_LOOP
            && query.postType === 'post'
        );
    },
    icon: 'schedule',
    allowedControls: [ 'inherit', 'order', 'taxQuery', 'search', 'author' ],
    attributes: {
        namespace: UCSC_NEWS_ARTICLE_LOOP,
        query: {
            perPage: 9,
            pages: 0,
            offset: 0,
            postType: 'post',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: true,
        },
    },
    scope: [ 'inserter' ],
    innerBlocks: [
    ['core/post-template',{"layout":{"type":"grid","columnCount":3}},
        [
            ['core/group', {"className":"ucsc__card ucsc__card--query-loop","style":{"spacing":{"padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}},[
                
                [ 'core/post-featured-image', {"isLink":true,"width":"","height":"","sizeSlug":"sixteen-nine","className":"ucsc-query-loop__image"}],
                [ 'core/post-terms', {"term":"category","className":"ucsc-query-loop__category","style":{"elements":{"link":{"color":{"text":"var:preset|color|ucsc-secondary-blue"}}},"spacing":{"margin":{"top":"0","right":"0","bottom":"0","left":"0"}}},"textColor":"black","fontSize":"small"}],           
                [ 'core/post-title', {"isLink":true,"style":{"spacing":{"margin":{"top":"0","right":"0","left":"0","bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|ucsc-secondary-blue"}}}},"textColor":"black","fontSize":"three"} ],
                [ 'core/post-date', {"className":"ucsc-query-loop__post-date","style":{"spacing":{"margin":{"bottom":"0","top":"0","right":"0","left":"0"}}},"textColor":"black","fontSize":"base"}],
                [ 'core/post-excerpt', {"className":"ucsc-query-loop__excerpt","style":{"spacing":{"margin":{"top":"0","right":"0","bottom":"0","left":"0"}}}}]
                                
            ]]
        ],
    ],
    [ 'core/query-pagination' ],
    [ 'core/query-no-results' ],
],
    }
);



