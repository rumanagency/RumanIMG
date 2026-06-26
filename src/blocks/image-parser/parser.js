/**
 * Maps every known hostname (CDN and viewer) to its canonical site name and
 * whether the src URL carries a size token that must be stripped.
 *
 * When adding a new image host:
 *   1. Add entries here for both the CDN host and the viewer host.
 *   2. Add a link to HOSTING_SITES[] in edit.js.
 */
const HOST_MAP = {
	'i.imgur.com':        { site: 'imgur.com',      needsFix: false },
	'imgur.com':          { site: 'imgur.com',      needsFix: false },
	'i.ibb.co':           { site: 'ibb.co',         needsFix: false },
	'ibb.co':             { site: 'ibb.co',         needsFix: false },
	'iili.io':            { site: 'freeimage.host', needsFix: true  },
	'freeimage.host':     { site: 'freeimage.host', needsFix: false },
	'i.postimg.cc':       { site: 'postimg.cc',     needsFix: false },
	'postimg.cc':         { site: 'postimg.cc',     needsFix: false },
	'thumbs2.imgbox.com': { site: 'imgbox.com',     needsFix: true  },
	'images2.imgbox.com': { site: 'imgbox.com',     needsFix: false },
	'imgbox.com':         { site: 'imgbox.com',     needsFix: false },
};

/**
 * Detect the hosting service from any URL.
 *
 * @param {string} url
 * @returns {{ site: string, needsFix: boolean }|null}
 */
export function detectHost( url ) {
	try {
		const { hostname } = new URL( url );
		return HOST_MAP[ hostname ] ?? null;
	} catch {
		return null;
	}
}

/**
 * Transform a CDN src URL to its full-resolution equivalent.
 * Each host that sets needsFix:true has its own rule here.
 *
 * @param {string}  src
 * @param {{ site: string, needsFix: boolean }} hostInfo
 * @returns {string}
 */
export function fixImageUrl( src, hostInfo ) {
	if ( ! hostInfo.needsFix ) return src;

	if ( hostInfo.site === 'freeimage.host' ) {
		// "https://iili.io/CAGYfNj.md.jpg"  →  "https://iili.io/CAGYfNj.jpg"
		return src.replace( /\.(md|th|sm|lg)(\.[a-z]+)$/i, '$2' );
	}

	if ( hostInfo.site === 'imgbox.com' ) {
		// "https://thumbs2.imgbox.com/0d/80/p6WPPIdV_t.jpg"
		//   →  "https://images2.imgbox.com/0d/80/p6WPPIdV_o.jpg"
		return src
			.replace( 'thumbs2.imgbox.com', 'images2.imgbox.com' )
			.replace( /_t\.([a-z]+)$/i, '_o.$1' );
	}

	return src;
}

/** True when the URL path ends with a common image extension. */
function isDirectImageUrl( url ) {
	return /\.(jpg|jpeg|png|gif|webp|avif|bmp)(\?.*)?$/i.test( url );
}

/**
 * Construct the viewer page URL from a CDN image URL.
 * Returns the CDN URL itself when no mapping is known.
 */
function pageUrlFromCdn( cdnUrl, site ) {
	try {
		const { pathname } = new URL( cdnUrl );
		if ( site === 'imgur.com' ) {
			// https://i.imgur.com/RaUTwBa.jpg  →  https://imgur.com/RaUTwBa
			const id = pathname.replace( /^\//, '' ).replace( /\.[^.]+$/, '' );
			if ( id ) return `https://imgur.com/${ id }`;
		}
	} catch {}
	return cdnUrl;
}

/**
 * Construct the original CDN image URL directly from a viewer page URL.
 * Returns null when the host requires a server-side fetch to resolve the URL.
 */
function cdnFromPageUrl( pageUrl, site ) {
	try {
		const { pathname } = new URL( pageUrl );
		if ( site === 'freeimage.host' ) {
			// https://freeimage.host/i/{ID}  →  https://iili.io/{ID}.jpg
			const id = pathname.replace( /^\/i\//, '' );
			if ( id ) return `https://iili.io/${ id }.jpg`;
		}
		if ( site === 'imgur.com' ) {
			// https://imgur.com/{ID}  →  https://i.imgur.com/{ID}.jpg
			const id = pathname.replace( /^\//, '' );
			if ( id ) return `https://i.imgur.com/${ id }.jpg`;
		}
	} catch {}
	return null; // null = needs server-side AJAX resolution
}

// ── Format-specific parsers ───────────────────────────────────────────────────

/**
 * Parse HTML embed codes produced by imgur, ibb, freeimage.host and postimg.cc.
 * Extracts every <a href><img src> pair and normalises URLs.
 */
function parseHtmlEmbed( rawHtml ) {
	const parser = new DOMParser();
	const doc    = parser.parseFromString( rawHtml, 'text/html' );
	const items  = [];

	for ( const anchor of doc.querySelectorAll( 'a[href]' ) ) {
		const img = anchor.querySelector( 'img[src]' );
		if ( ! img ) continue;

		const pageUrl = anchor.getAttribute( 'href' )?.trim();
		const rawSrc  = img.getAttribute( 'src' )?.trim();
		if ( ! pageUrl || ! rawSrc ) continue;

		const hostInfo = detectHost( rawSrc ) ?? detectHost( pageUrl );
		if ( ! hostInfo ) continue;

		items.push( {
			pageUrl,
			imgUrl:   fixImageUrl( rawSrc, hostInfo ),
			siteName: hostInfo.site,
		} );
	}

	return items;
}

/**
 * Parse a plain list of URLs — one per line.
 *
 * Handles two sub-formats:
 *   • Direct CDN image URLs  (imgur CDN, postimg CDN, freeimage CDN)
 *   • Viewer page URLs       (ibb.co/… → needs AJAX; freeimage.host/i/… → constructed locally)
 */
function parsePlainUrls( rawText ) {
	const lines = rawText
		.split( '\n' )
		.map( ( l ) => l.trim() )
		.filter( ( l ) => l.startsWith( 'http' ) );

	const items = [];

	for ( const line of lines ) {
		const hostInfo = detectHost( line );
		if ( ! hostInfo ) continue;

		if ( isDirectImageUrl( line ) ) {
			// ── Direct CDN URL ───────────────────────────────────────────
			const imgUrl = fixImageUrl( line, hostInfo );
			items.push( {
				pageUrl:  pageUrlFromCdn( imgUrl, hostInfo.site ),
				imgUrl,
				siteName: hostInfo.site,
			} );
		} else {
			// ── Viewer page URL ──────────────────────────────────────────
			// Try to build the CDN URL directly; fall back to a placeholder
			// (the placeholder will be resolved via AJAX for hosts that need it).
			const constructed = cdnFromPageUrl( line, hostInfo.site );
			items.push( {
				pageUrl:  line,
				imgUrl:   constructed ?? line,
				siteName: hostInfo.site,
			} );
		}
	}

	return items;
}

// ── Public API ────────────────────────────────────────────────────────────────

/**
 * Main entry point.  Accepts either HTML embed codes or a plain URL list and
 * returns a normalised array of { pageUrl, imgUrl, siteName } objects.
 *
 * @param {string} raw
 * @returns {Array<{ pageUrl: string, imgUrl: string, siteName: string }>}
 */
export function parseEmbedCode( raw ) {
	if ( ! raw?.trim() ) return [];

	// Route to the correct sub-parser based on content type.
	return /<[a-z]/i.test( raw )
		? parseHtmlEmbed( raw )
		: parsePlainUrls( raw );
}

/**
 * Serialise the items array into the normalised one-image-per-line HTML string.
 *
 * @param {Array<{ pageUrl: string, imgUrl: string, siteName: string }>} items
 * @returns {string}
 */
export function buildOutput( items ) {
	return items
		.map(
			( { pageUrl, imgUrl, siteName } ) =>
				`<a href="${ pageUrl }"><img src="${ imgUrl }" title="source: ${ siteName }" /></a>`
		)
		.join( '\n' );
}
