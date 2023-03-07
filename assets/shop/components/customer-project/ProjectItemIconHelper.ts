import ProjectItem from "./model/ProjectItem";
import {DEFAULT_PROJECT_ITEM_ICON, ProjectItemIcons} from "./model/types/ProjectItemIcons";
import {Taxon} from "./model/types/Taxon";

const Selectors = {
  ICON_LINK_CLASS: '.ps-project-item__icon-link',
};

export default class ProjectItemIconHelper {
  public static updateProjectIcon(projectItem: ProjectItem) {
    if (projectItem.taxons.length > 0) {
      const iconLink = projectItem.elmt.querySelector(Selectors.ICON_LINK_CLASS);

      if (typeof iconLink !== 'undefined') {
        // Remove default icon from icon link
        let baseUrl = (<SVGAnimatedString>iconLink.href).baseVal.replace(DEFAULT_PROJECT_ITEM_ICON, '');

        // Foreach mapped taxon => icon
        return Object.keys(ProjectItemIcons).find((key: Taxon) => {
          if (projectItem.hasTaxon(key)) {
            (<SVGAnimatedString>iconLink.href).baseVal = baseUrl + ProjectItemIcons[key];
            return true;
          }
        });
      }
    }
  }
}
