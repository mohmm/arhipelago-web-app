/**
 * Props for a link that opens another website in a new tab.
 */
export const EXTERNAL_LINK_PROPS = {
    target: '_blank',
    rel: 'noopener noreferrer',
} as const;

/**
 * The co-financed project, printed under the European funding emblems when known.
 */
export const STRUCTURAL_FUNDS = {
    projectName: '',
    contractNumber: '',
} as const;

/**
 * The ANPC alternative dispute resolution portal, which Order 449/2022 requires
 * a Romanian trader's website to link to with its pictogram.
 */
export const ANPC_SAL_URL = 'https://reclamatiisal.anpc.ro/';
