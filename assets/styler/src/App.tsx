import React, {useEffect, useState} from 'react';
import './App.scss';
import Container from 'react-bootstrap/Container'
import Spinner from 'react-bootstrap/Spinner'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'
import resizeIcon from './assets/img/resize.svg';
import closeIcon from './assets/img/close.svg';
import {ControlPanel} from "./components/ControlPanel";
import {SceneConfig} from "./types/domain";
import {observer} from "mobx-react-lite";
import {AppManager} from "./domain/AppManager";
import {ViewsCarousel} from "./components/ViewsCarousel";
import {ErrorsContainer} from "./components/ErrorsContainer";

const dataSceneId = document.getElementById('react-styler')?.getAttribute('data-scene');
const dataOptionUrl = document.getElementById('react-styler')?.getAttribute('data-options-url');
const dataUserId = document.getElementById('react-styler')?.getAttribute('data-user-id');
const dataSessionId = document.getElementById('react-styler')?.getAttribute('data-session-id');

const app = new AppManager({
  sceneId: dataSceneId ? parseInt(dataSceneId) : 7,
  optionsApiUrl: dataOptionUrl ?? 'http://localhost:8000/api/options',
  userConfig: {
    userId: dataUserId ?? undefined,
    sessionId: dataSessionId ?? undefined
  }
});

const App = observer(() => {
  const [scene, setScene] = useState<SceneConfig | undefined>(undefined);
  const [bgColor, setBgColor] = useState<string>();

  const [isExpanded, setIsExpanded] = useState<boolean>(false);
  const [isLoaded, setIsLoaded] = useState<boolean>(false);
  const [sizePhoto, setSizePhoto] = useState<string>('initScreen');


  const handleExpandState = (e: React.ChangeEvent<any>) => {
    if (isExpanded) {
      setSizePhoto('initScreen');
    } else {
      setSizePhoto('fullScreen');
    }
    setIsExpanded(!isExpanded)
  }

  const handleChange = (filterState: any) => {
    if (filterState && filterState.bgColor) {
      setBgColor(filterState.bgColor);
    }
    // setImages(partieHauteState);
  };

  useEffect(() => {
    app.init().then(() => {
      setScene(app.sceneConfig);
      setIsLoaded(true);
    });
  }, []);

  return (
    <>
      <div className="App h-100 d-none d-md-block">
        <ErrorsContainer manager={app.errorManager}/>
        <Container className="mw-100 w-100">
          {!isLoaded &&
            <Row className="loaderContainer align-items-center">
              <Col>
                <Spinner
                  as="span"
                  animation="border"
                  role="status"
                  aria-hidden="true"
                />
                <span className="d-block mt-3"> Patience, nous imaginons votre prochaine cuisine...</span>
              </Col>
            </Row>
          }

          <Row>
            {isLoaded && typeof scene !== 'undefined' &&
            <>
              {!isExpanded &&
                <div className="styler__left-nav">
                  <ControlPanel view={app.viewManager.selectionView} isLoadingView={app.isLoadingView}/>
                </div>
              }

              <div className={`styler__carousel ${sizePhoto}`} style={{background: bgColor}}>
                <Button className="expandBtn" onClick={handleExpandState}>
                  {isExpanded &&
                    <img src={closeIcon} width={16} alt="Close"/>
                  }
                  {!isExpanded &&
                    <img src={resizeIcon} width={16} alt="Open"/>
                  }
                </Button>
                <ViewsCarousel sceneViews={app.configManager.sceneViews}
                               isLoadingView={app.isLoadingView}
                               handleChange={(sceneView) => {app.configManager.selectSceneView(sceneView)}}/>
              </div>
            </>
            }
          </Row>
        </Container>
      </div>
      <div className="App d-md-none mobileView">
        <Row className="align-items-center u-padding-t-12">
          <Col xs={12} className="text-center">
            <span>
              Pour une expérience optimale, utilisez le Plum Styler depuis votre ordinateur.
            </span>
          </Col>
        </Row>
      </div>
    </>
  );
})

export default App;
