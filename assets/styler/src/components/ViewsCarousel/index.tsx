import './style.scss';
import Carousel from 'react-bootstrap/Carousel'
import {SceneView} from "../../types/domain/SceneView";
import {observer} from "mobx-react-lite";

interface ViewsCarouselProps {
  sceneViews: SceneView[],
  handleChange: (sceneView: SceneView) => void,
  isLoadingView?: boolean,
}

export const ViewsCarousel = observer(({sceneViews, handleChange}: ViewsCarouselProps) => {
  const onSelect = (selectedIndex: number) => {
    handleChange(sceneViews[selectedIndex]);
  }

  return (
    <>
      <Carousel interval={null} onSelect={onSelect}>
        {sceneViews
          .map((view: SceneView, index) => (
              <Carousel.Item key={view.code}>
                <img
                  className="d-block "
                  src={view.uri}
                  alt={`View #${index}`}
                />
              </Carousel.Item>
            )
          )}
      </Carousel>
    </>
  )
})
