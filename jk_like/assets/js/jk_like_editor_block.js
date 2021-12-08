const { __, setLocaleData } = wp.i18n;
const { registerBlockType } = wp.blocks;
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType( 'top_like/toc', {
    title: __( 'Top likes', 'top_like' ),
    icon: 'list-view',
    category: 'layout',
    edit: function( props ) {
        return (
            <p className={ props.className }>
                <ServerSideRender
                    block="top_like/toc"
                    attributes={ props.attributes }
                />
            </p>
        );
    },
    save: props => {
        return null;
    },
} );