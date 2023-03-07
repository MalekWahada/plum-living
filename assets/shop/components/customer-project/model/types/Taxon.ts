
export enum Taxon {
  FACADE = 'facade',
  FACADE_PAX = 'pax',
  FACADE_METOD = 'metod',
  FACADE_METOD_DOOR = 'metod_door',
  FACADE_PAX_DOOR = 'pax_door',
  FACADE_PAX_PANEL = 'pax_panel',
  FACADE_METOD_PANEL = 'metod_panel',
  FACADE_METOD_DRAWER = 'metod_drawer',
  FACADE_METOD_BASEBOARD = 'metod_baseboard',
  PAINT = 'peinture',
  ACCESSORY = 'accessoires',
  ACCESSORY_HANDLE = 'accessoires_handle',
  TAP = 'tap'
}

export enum AddProjectItemTaxon {
  FACADE_METOD = Taxon.FACADE_METOD,
  FACADE_PAX = Taxon.FACADE_PAX,
  PAINT = Taxon.PAINT,
  ACCESSORY = Taxon.ACCESSORY,
  ACCESSORY_HANDLE = Taxon.ACCESSORY_HANDLE,
  TAP = Taxon.TAP
}
