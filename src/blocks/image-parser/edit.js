import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, Button, ExternalLink, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { parseEmbedCode, buildOutput } from './parser';

/**
 * Quick-access links shown in the block sidebar.
 * When adding a new image host, add its entry here AND update HOST_MAP in parser.js.
 */
const HOSTING_SITES = [
	{ label: 'imgur.com',      url: 'https://imgur.com/upload'   },
	{ label: 'imgbb.com',      url: 'https://imgbb.com/'         },
	{ label: 'freeimage.host', url: 'https://freeimage.host/'    },
	{ label: 'postimg.cc',     url: 'https://postimg.cc/'        },
	{ label: 'imgbox.com',     url: 'https://imgbox.com/'        },
];

/**
 * For hosts that need server-side URL resolution to get the original image.
 * Hosts NOT listed here are assumed to already give full-resolution src URLs.
 */
const NEEDS_RESOLUTION = [ 'ibb.co', 'imgbox.com' ];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const blockProps = useBlockProps( { className: 'rumanimg-editor' } );
	const { rawCode, parsedItems } = attributes;

	const [ copied,    setCopied    ] = useState( false );
	const [ pasted,    setPasted    ] = useState( false );
	const [ resolving, setResolving ] = useState( false );

	const { insertBlocks } = useDispatch( 'core/block-editor' );
	const blockIndex    = useSelect( ( s ) => s( 'core/block-editor' ).getBlockIndex( clientId ),         [ clientId ] );
	const rootClientId  = useSelect( ( s ) => s( 'core/block-editor' ).getBlockRootClientId( clientId ),  [ clientId ] );

	// ── Paste handler ────────────────────────────────────────────────────────

	function handleChange( value ) {
		const items = parseEmbedCode( value );
		setAttributes( { rawCode: value, parsedItems: items } );
		// Kick off resolution for hosts that need it (imgbb etc.)
		if ( items.length ) resolveOriginals( items );
	}

	// ── Server-side URL resolver (for imgbb originals) ────────────────────

	async function resolveOriginals( items ) {
		const toResolve = items.filter( ( i ) => NEEDS_RESOLUTION.includes( i.siteName ) );
		if ( ! toResolve.length ) return;

		setResolving( true );

		const resolved = await Promise.all(
			items.map( async ( item ) => {
				if ( ! NEEDS_RESOLUTION.includes( item.siteName ) ) return item;

				try {
					const body = new FormData();
					body.append( 'action',  'rumanimg_resolve_url' );
					body.append( 'nonce',   rumanimg_block.resolve_nonce );
					body.append( 'pageUrl', item.pageUrl );

					const res  = await fetch( rumanimg_block.ajax_url, { method: 'POST', body } );
					const json = await res.json();

					if ( json.success && json.data.url ) {
						return { ...item, imgUrl: json.data.url };
					}
				} catch {}

				return item;
			} )
		);

		setResolving( false );
		setAttributes( { parsedItems: resolved } );
	}

	// ── Actions ───────────────────────────────────────────────────────────────

	function handleClear() {
		setAttributes( { rawCode: '', parsedItems: [] } );
	}

	function handleCopy() {
		navigator.clipboard.writeText( buildOutput( parsedItems ) ).then( () => {
			setCopied( true );
			setTimeout( () => setCopied( false ), 2000 );
		} );
	}

	function handlePasteToPost() {
		const htmlBlock = createBlock( 'core/html', { content: buildOutput( parsedItems ) } );
		insertBlocks( [ htmlBlock ], blockIndex + 1, rootClientId, false );
		setPasted( true );
		setTimeout( () => setPasted( false ), 2000 );
	}

	const count    = parsedItems.length;
	const hasInput = rawCode.trim().length > 0;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Image Hosting Sites', 'rumanimg' ) }
					initialOpen={ true }
				>
					<p className="rumanimg-sidebar__label">
						{ __( 'Open a site to upload your photos:', 'rumanimg' ) }
					</p>
					<div className="rumanimg-sidebar__links">
						{ HOSTING_SITES.map( ( { label, url } ) => (
							<ExternalLink
								key={ label }
								href={ url }
								className="rumanimg-sidebar__link"
							>
								{ label }
							</ExternalLink>
						) ) }
					</div>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>

				{ /* ── Header ── */ }
				<div className="rumanimg-editor__header">
					<svg className="rumanimg-editor__icon" viewBox="0 0 490 500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<polygon points="489.52 0 489.52 130.26 371.87 250.6 249.26 127.99 126.51 250.74 251.25 375.49 130.26 500 0 500 0 191.17 191.17 0 489.52 0"/>
						<polygon points="251.27 375.21 371.86 250.59 489.52 375.03 489.52 500 371.58 500 251.27 375.21"/>
					</svg>
					<span className="rumanimg-editor__title">
						{ __( 'Ruman IMG — Image Parser', 'rumanimg' ) }
					</span>
				</div>

				{ /* ── Paste area ── */ }
				<TextareaControl
					label={ __( 'Paste embed code', 'rumanimg' ) }
					help={ __( 'Supports: imgur · ibb · freeimage.host · postimg.cc · imgbox', 'rumanimg' ) }
					value={ rawCode }
					onChange={ handleChange }
					rows={ 6 }
					placeholder={ __( 'Paste your embed HTML here…', 'rumanimg' ) }
					className="rumanimg-editor__textarea"
				/>

				{ /* ── Status badge ── */ }
				{ count > 0 && (
					<div className="rumanimg-editor__status is-success">
						{ resolving
							? <><Spinner /> { __( 'Fetching original URLs…', 'rumanimg' ) }</>
							: <>✓ { count } { count === 1 ? __( 'image found', 'rumanimg' ) : __( 'images found', 'rumanimg' ) }</>
						}
					</div>
				) }

				{ hasInput && count === 0 && (
					<div className="rumanimg-editor__status is-warning">
						{ __( 'No supported images detected — check your embed code.', 'rumanimg' ) }
					</div>
				) }

				{ /* ── Thumbnail preview ── */ }
				{ count > 0 && (
					<div className="rumanimg-editor__preview">
						{ parsedItems.map( ( item, i ) => (
							<a
								key={ i }
								href={ item.pageUrl }
								target="_blank"
								rel="noreferrer"
								className="rumanimg-editor__thumb"
								title={ `source: ${ item.siteName }` }
							>
								<img src={ item.imgUrl } alt="" loading="lazy" />
							</a>
						) ) }
					</div>
				) }

				{ /* ── Action buttons ── */ }
				{ count > 0 && (
					<div className="rumanimg-editor__actions">
						<Button
							variant="primary"
							onClick={ handlePasteToPost }
							disabled={ pasted || resolving }
							className="rumanimg-editor__btn-insert"
						>
							{ pasted
								? __( '✓ Inserted!', 'rumanimg' )
								: __( 'Paste into post', 'rumanimg' ) }
						</Button>
						<Button
							variant="secondary"
							onClick={ handleCopy }
							disabled={ copied || resolving }
						>
							{ copied
								? __( '✓ Copied!', 'rumanimg' )
								: __( 'Copy output code', 'rumanimg' ) }
						</Button>
						<Button
							variant="tertiary"
							isDestructive
							onClick={ handleClear }
						>
							{ __( 'Clear', 'rumanimg' ) }
						</Button>
					</div>
				) }

			</div>
		</>
	);
}
