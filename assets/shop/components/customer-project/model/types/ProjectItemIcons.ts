import {Taxon} from "./Taxon";

// caution: order is important (as tap is also an accessory). First match is used
export const ProjectItemIcons: { [key in Taxon]?: string } = {
  [Taxon.FACADE_METOD_DRAWER]: 'tiroir',
  [Taxon.FACADE_METOD_DOOR]: 'door',
  [Taxon.FACADE_PAX_DOOR]: 'door',
  [Taxon.FACADE]: 'panneau',
  [Taxon.PAINT]: 'paint',
  [Taxon.TAP]: 'tap',
  [Taxon.ACCESSORY]: 'accessoire',
};

export const DEFAULT_PROJECT_ITEM_ICON = ProjectItemIcons.accessoires;
