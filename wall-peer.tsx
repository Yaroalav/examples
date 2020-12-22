import React, {Component, RefObject} from "react";
import Peer, {MediaConnection} from "peerjs";
import MediaPresenter from "../../../lib/media-presenter/media-presenter";
import Signaling from "../../../lib/signaling";
import DataPresenter from "../../../lib/data-presenter";
// import { connect } from "react-redux";
// import { signIn } from "../../../store/auth/actions";

interface Props {
    Connection: MediaConnection,
    Signaling?: Signaling,
    OnError?: (peer:string) => void;
}

interface State {
    ShowControls: boolean;
}

class WallPeer extends Component<Props, State> {

    private mediaPresenter: MediaPresenter;
    private videoTag: RefObject<HTMLVideoElement>;
    private dataPresenter?: DataPresenter;

    public constructor (props: Props) {
        super(props);

        this.videoTag = React.createRef();

        this.mediaPresenter = new MediaPresenter(props.Connection, "main");
        this.mediaPresenter.OnClose = (peer, _channelName) => props.OnError?.(peer);

        this.prepareDataConnection();
    }


    private prepareDataConnection() {
        if (this.props.Signaling === undefined) return;

        let dataConnection = this.props.Signaling.DataCall(this.props.Connection.peer);
        if (dataConnection !== undefined) {
            this.dataPresenter = new DataPresenter(dataConnection, "default");
            this.dataPresenter.OnClose = (_peer, _channelName) => this.props.OnError?.(this.props.Connection.peer);
            this.dataPresenter.OnConnectionEstablished = (_peer, _channelName) => this.setState({ShowControls: true});
        }
    }


    componentDidMount() {
        if (this.videoTag.current !== null)
            this.mediaPresenter.ShowRemoteOn(this.videoTag.current);
    }

    public render() {
        return (
            <div>
                <video ref={this.videoTag}></video>
                <div>
                    <button onClick={this.mediaPresenter.MuteRemote}>Mute</button>
                    <button onClick={this.mediaPresenter.UnMuteRemote}>UnMute</button>
                </div>
                <div>
                { this.ShowControls() }
                </div>
            </div>
        );
    }

    private ShowControls() : JSX.Element | null {
        if (!this.state.ShowControls) return null;
        return (
            <>
                <button onClick={this.dataPresenter?.SendMuteAll}>MuteAll</button>
                <button onClick={this.dataPresenter?.SendUnMuteAll}>UnMuteAll</button>
            </>
        )
    }

}

export default WallPeer;
